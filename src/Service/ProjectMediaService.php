<?php

namespace App\Service;

use App\Entity\Project;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProjectMediaService
{
    public function __construct(
        private readonly string $imagesDirectory,
        private readonly SluggerInterface $slugger,
        private readonly Filesystem $filesystem,
    ) {
    }

    public function handleUploads(FormInterface $form, Project $project): void
    {
        $this->filesystem->mkdir($this->imagesDirectory);

        $currentImages = $project->getImages();
        $removedImages = (array) $form->get('removeImages')->getData();

        if ($removedImages !== []) {
            foreach ($removedImages as $filename) {
                if (in_array($filename, $currentImages, true)) {
                    $this->deleteFile($filename);
                }
            }

            $currentImages = array_values(array_filter(
                $currentImages,
                static fn (string $filename): bool => !in_array($filename, $removedImages, true)
            ));
        }

        if ((bool) $form->get('removeBannerImage')->getData()) {
            $this->deleteFile($project->getBannerImage());
            $project->setBannerImage(null);
        }

        /** @var UploadedFile|null $bannerImageFile */
        $bannerImageFile = $form->get('bannerImageFile')->getData();
        if ($bannerImageFile instanceof UploadedFile) {
            $this->deleteFile($project->getBannerImage());
            $project->setBannerImage($this->uploadFile($bannerImageFile, $project->getTitle() ?? 'project-banner', 'banner'));
        }

        /** @var UploadedFile[]|null $galleryImages */
        $galleryImages = $form->get('galleryImages')->getData();
        if (is_iterable($galleryImages)) {
            foreach ($galleryImages as $imageFile) {
                if ($imageFile instanceof UploadedFile) {
                    $currentImages[] = $this->uploadFile($imageFile, $project->getTitle() ?? 'project-gallery', 'gallery');
                }
            }
        }

        $project->setImages(array_values(array_unique($currentImages)));
    }

    public function deleteProjectMedia(Project $project): void
    {
        $this->deleteFile($project->getBannerImage());

        foreach ($project->getImages() as $image) {
            $this->deleteFile($image);
        }
    }

    private function uploadFile(UploadedFile $file, string $projectTitle, string $prefix): string
    {
        $slug = (string) $this->slugger->slug(pathinfo($projectTitle, PATHINFO_FILENAME))->lower();
        $slug = $slug !== '' ? $slug : $prefix;

        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';
        $filename = sprintf('%s-%s-%s.%s', $prefix, $slug, $this->generateUuidV4(), $extension);

        while (is_file($this->imagesDirectory . '/' . $filename)) {
            $filename = sprintf('%s-%s-%s.%s', $prefix, $slug, $this->generateUuidV4(), $extension);
        }

        try {
            $file->move($this->imagesDirectory, $filename);
        } catch (FileException $exception) {
            throw new \RuntimeException('Impossible de televerser le media du projet.', 0, $exception);
        }

        return $filename;
    }

    private function deleteFile(?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $path = $this->imagesDirectory . '/' . $filename;

        if (is_file($path)) {
            $this->filesystem->remove($path);
        }
    }

    private function generateUuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
