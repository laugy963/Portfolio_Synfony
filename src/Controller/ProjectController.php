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
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $project = new Project();
            $project->setTitle($request->request->get('title'));
            $project->setSmallDescription($request->request->get('smallDescription'));
            $project->setDescription($request->request->get('description'));
            $project->setLink($request->request->get('link'));
            $project->setTechnologies($request->request->get('technologies'));
            $project->setMadeBy($request->request->get('madeBy'));

            // Gestion de l'image principale (bannière)
            $bannerImageFile = $request->files->get('bannerImage');
            if ($bannerImageFile) {
                $newFilename = uniqid().'.'.$bannerImageFile->guessExtension();
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

            // Gestion des autres images
            $imagesFiles = $request->files->get('images');
            $imagesNames = [];
            if ($imagesFiles) {
                foreach ($imagesFiles as $imageFile) {
                    $imgFilename = uniqid().'.'.$imageFile->guessExtension();
                    try {
                        $imageFile->move(
                            $this->getParameter('images_directory'),
                            $imgFilename
                        );
                        $imagesNames[] = $imgFilename;
                    } catch (FileException $e) {
                        // Gérer l'erreur
                    }
                }
                $project->setImages($imagesNames); // Assurez-vous que le champ est bien un array ou JSON
            }

            $entityManager->persist($project);
            $entityManager->flush();

            $this->addFlash('success', 'Projet créé avec succès !');
            return $this->redirectToRoute('app_project_new');
        }

        return $this->render('project/new.html.twig');
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
}
