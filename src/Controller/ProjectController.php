<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use App\Service\ProjectMediaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/projects')]
class ProjectController extends AbstractController
{
    #[Route('/', name: 'app_project_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(ProjectRepository $projectRepository): Response
    {
        return $this->render('project/index.html.twig', [
            'projects' => $projectRepository->findAllOrderedByPosition(),
        ]);
    }

    #[Route('/new', name: 'app_project_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ProjectRepository $projectRepository,
        ProjectMediaService $projectMediaService,
    ): Response {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $project->setPosition($projectRepository->getNextPosition());
                $projectMediaService->handleUploads($form, $project);

                $entityManager->persist($project);
                $entityManager->flush();

                $this->addFlash('success', 'Projet cree avec succes.');

                return $this->redirectToRoute('app_project_index');
            } catch (\RuntimeException $exception) {
                $form->addError(new FormError($exception->getMessage()));
            }
        }

        return $this->render('project/new.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_project_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(Project $project): Response
    {
        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/public/{id}', name: 'project_public_show', methods: ['GET'])]
    public function publicShow(Project $project): Response
    {
        return $this->render('project/public_show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_project_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        Request $request,
        Project $project,
        EntityManagerInterface $entityManager,
        ProjectMediaService $projectMediaService,
    ): Response {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $projectMediaService->handleUploads($form, $project);
                $entityManager->flush();

                $this->addFlash('success', 'Projet modifie avec succes.');

                return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
            } catch (\RuntimeException $exception) {
                $form->addError(new FormError($exception->getMessage()));
            }
        }

        return $this->render('project/edit.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_project_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        Request $request,
        Project $project,
        EntityManagerInterface $entityManager,
        ProjectMediaService $projectMediaService,
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $project->getId(), (string) $request->request->get('_token'))) {
            $projectMediaService->deleteProjectMedia($project);
            $entityManager->remove($project);
            $entityManager->flush();

            $this->addFlash('success', 'Projet supprime avec succes.');
        } else {
            $this->addFlash('error', 'Jeton de securite invalide. Veuillez reessayer.');
        }

        return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/check-filename', name: 'app_project_check_filename', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function checkFilename(Request $request, ProjectRepository $projectRepository): Response
    {
        $filename = trim((string) $request->request->get('filename'));
        $excludeProjectId = $request->request->getInt('projectId') ?: null;

        if ($filename === '') {
            return $this->json(['exists' => false]);
        }

        return $this->json([
            'exists' => $projectRepository->mediaFilenameExists($filename, $excludeProjectId),
        ]);
    }

    #[Route('/api/reorder', name: 'app_project_reorder', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function reorderProjects(
        Request $request,
        EntityManagerInterface $entityManager,
        ProjectRepository $projectRepository,
    ): Response {
        $csrfToken = $request->headers->get('X-CSRF-Token');

        if (!$this->isCsrfTokenValid('project_reorder', (string) $csrfToken)) {
            return $this->json([
                'success' => false,
                'message' => 'Jeton CSRF invalide.',
            ], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode((string) $request->getContent(), true);

        if (!is_array($data) || !isset($data['projectIds']) || !is_array($data['projectIds'])) {
            return $this->json([
                'success' => false,
                'message' => 'Donnees invalides.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $projectIds = array_values(array_unique(array_map('intval', $data['projectIds'])));
        $projects = $projectRepository->findBy(['id' => $projectIds]);

        if (count($projects) !== count($projectIds)) {
            return $this->json([
                'success' => false,
                'message' => 'Un ou plusieurs projets sont introuvables.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $projectsById = [];
        foreach ($projects as $project) {
            $projectsById[$project->getId()] = $project;
        }

        foreach ($projectIds as $position => $projectId) {
            $projectsById[$projectId]->setPosition($position + 1);
        }

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Ordre sauvegarde avec succes.',
        ]);
    }
}
