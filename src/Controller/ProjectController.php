<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/projects')]
#[IsGranted('ROLE_ADMIN')]
class ProjectController extends AbstractController
{
    #[Route('/', name: 'app_project_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {
        return $this->render('project/index.html.twig', [
            'projects' => $projectRepository->findBy([], ['createdAt' => 'DESC']),
            'controller_name' => 'ProjectController',
        ]);
    }

    #[Route('/new', name: 'app_project_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ProjectRepository $projectRepository): Response
    {
        if ($request->isMethod('POST')) {
            $project = new Project();
            $title = $request->request->get('title');
            $smallDescription = $request->request->get('smallDescription');
            $description = $request->request->get('description');
            $link = $request->request->get('link');
            $technologies = $request->request->get('technologies');
            $madeBy = $request->request->get('madeBy');

            $hasError = false;

            // Validation des champs
            if (!$title || strlen(trim($title)) < 3) {
                $this->addFlash('error_title', 'Le titre est obligatoire (min 3 caractères).');
                $hasError = true;
            }
            if ($smallDescription && strlen($smallDescription) > 255) {
                $this->addFlash('error_smallDescription', 'La petite description est trop longue (max 255 caractères).');
                $hasError = true;
            }
            if (!$description || strlen(trim($description)) < 10) {
                $this->addFlash('error_description', 'La description détaillée est obligatoire (min 10 caractères).');
                $hasError = true;
            }
            if ($technologies && strlen($technologies) > 2000) {
                $this->addFlash('error_technologies', 'Le champ "Technologies" est trop long (max 2000 caractères).');
                $hasError = true;
            }
            if ($madeBy && strlen($madeBy) > 255) {
                $this->addFlash('error_madeBy', 'Le champ "Projet réalisé par" est trop long (max 255 caractères).');
                $hasError = true;
            }

            // Récupérer tous les noms de fichiers existants
            $allProjects = $projectRepository->findAll();
            $existingFilenames = [];
            foreach ($allProjects as $p) {
                if ($p->getBannerImage()) {
                    $existingFilenames[] = $p->getBannerImage();
                }
                if (is_array($p->getImages())) {
                    $existingFilenames = array_merge($existingFilenames, $p->getImages());
                }
            }

            // Gestion de l'image principale (bannière)
            $bannerImageFile = $request->files->get('bannerImage');
            $newFilename = null;
            if ($bannerImageFile) {
                $originalFilename = pathinfo($bannerImageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $bannerImageFile->guessExtension();
                $newFilename = $originalFilename . '.' . $extension;

                // Vérifier si le nom existe déjà
                if (in_array($newFilename, $existingFilenames)) {
                    $this->addFlash('filename_bannerImage', "L'image '$newFilename' existe déjà. Veuillez renommer votre fichier ou choisir une autre image.");
                    $hasError = true;
                }
            }

            // Gestion des autres images
            $imagesFiles = $request->files->get('images');
            $imagesNames = [];
            if ($imagesFiles) {
                foreach ($imagesFiles as $imageFile) {
                    if ($imageFile->getSize() > 0) { // Vérifier que le fichier n'est pas vide
                        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $imageFile->guessExtension();
                        $imgFilename = $originalFilename . '.' . $extension;

                        // Vérifier si le nom existe déjà
                        if (in_array($imgFilename, $existingFilenames) || in_array($imgFilename, $imagesNames)) {
                            $this->addFlash('filename_images', "L'image '$imgFilename' existe déjà. Veuillez renommer vos fichiers ou choisir d'autres images.");
                            $hasError = true;
                        } else {
                            $imagesNames[] = $imgFilename;
                        }
                    }
                }
            }

            // Si erreur, ajouter les valeurs en session et rediriger
            if ($hasError) {
                // Redirection vers la page de création, pas vers la liste
                $request->getSession()->set('form_data', [
                    'title' => $title,
                    'smallDescription' => $smallDescription,
                    'description' => $description,
                    'link' => $link,
                    'technologies' => $technologies,
                    'madeBy' => $madeBy,
                ]);
                return $this->redirectToRoute('app_project_new');
            }

            $project->setTitle($title);
            $project->setSmallDescription($smallDescription);
            $project->setDescription($description);
            $project->setLink($link);
            $project->setTechnologies($technologies);
            $project->setMadeBy($madeBy);

            // Upload et sauvegarde uniquement si pas d'erreur
            if ($bannerImageFile) {
                try {
                    $bannerImageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $project->setBannerImage($newFilename);
                } catch (FileException $e) {
                    // Gérer l'erreur
                }
            }

            if ($imagesFiles) {
                $uploadedImages = [];
                foreach ($imagesFiles as $idx => $imageFile) {
                    try {
                        $imageFile->move(
                            $this->getParameter('images_directory'),
                            $imagesNames[$idx]
                        );
                        $uploadedImages[] = $imagesNames[$idx];
                    } catch (FileException $e) {
                        // Gérer l'erreur
                    }
                }
                $project->setImages($uploadedImages);
            }

            $entityManager->persist($project);
            $entityManager->flush();

            $this->addFlash('success', 'Projet créé avec succès !');
            return $this->redirectToRoute('app_project_new');
        }

        // Récupérer les données du formulaire depuis la session
        $formData = $request->getSession()->get('form_data', []);
        $request->getSession()->remove('form_data'); // Nettoyer après utilisation

        return $this->render('project/new.html.twig', [
            'old' => $formData
        ]);
    }

    #[Route('/{id}', name: 'app_project_show', methods: ['GET'])]
    public function show(Project $project): Response
    {
        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Projet modifié avec succès !');

            return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project/edit.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_project_delete', methods: ['POST'])]
    public function delete(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->request->get('_token'))) {
            $entityManager->remove($project);
            $entityManager->flush();

            $this->addFlash('success', 'Projet supprimé avec succès !');
        }

        return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/check-filename', name: 'app_project_check_filename', methods: ['POST'])]
    public function checkFilename(Request $request, ProjectRepository $projectRepository): Response
    {
        $filename = $request->request->get('filename');
        
        if (!$filename) {
            return $this->json(['exists' => false]);
        }
        
        // Récupérer tous les noms de fichiers existants
        $allProjects = $projectRepository->findAll();
        $existingFilenames = [];
        foreach ($allProjects as $p) {
            if ($p->getBannerImage()) {
                $existingFilenames[] = $p->getBannerImage();
            }
            if (is_array($p->getImages())) {
                $existingFilenames = array_merge($existingFilenames, $p->getImages());
            }
        }
        
        $exists = in_array($filename, $existingFilenames);
        
        return $this->json(['exists' => $exists]);
    }
}
