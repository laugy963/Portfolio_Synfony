<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfilEditType;
use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/profil')]
#[IsGranted('ROLE_USER')]
class ProfilController extends AbstractController
{
    #[Route('/', name: 'app_profil_index', methods: ['GET'])]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non trouvé.');
        }

        return $this->render('profil/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/edit', name: 'app_profil_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non trouvé.');
        }

        // Sauvegarde de l'email original pour vérification
        $originalEmail = $user->getEmail();
        
        $form = $this->createForm(ProfilEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si l'email a été modifié
            $newEmail = $user->getEmail();
            $emailChanged = ($originalEmail !== $newEmail);
            
            if ($emailChanged) {
                // Si l'email a changé, marquer comme non vérifié
                $user->setIsVerified(false);
                $this->addFlash('warning', 'Votre email a été modifié. Vous devrez le vérifier à nouveau.');
            }

            try {
                $entityManager->flush();
                
                $successMessage = $emailChanged 
                    ? 'Profil mis à jour avec succès ! Un email de vérification sera envoyé à votre nouvelle adresse.'
                    : 'Profil mis à jour avec succès !';
                    
                $this->addFlash('success', $successMessage);

                return $this->redirectToRoute('app_profil_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour de votre profil.');
            }
        }

        return $this->render('profil/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/change-password', name: 'app_profil_change_password', methods: ['GET', 'POST'])]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non trouvé.');
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();
            $newPassword = $form->get('plainPassword')->getData();

            // Vérifier que le mot de passe actuel est correct
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
            } else {
                // Hasher le nouveau mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);

                try {
                    $entityManager->flush();
                    $this->addFlash('success', 'Mot de passe modifié avec succès !');
                    
                    return $this->redirectToRoute('app_profil_index', [], Response::HTTP_SEE_OTHER);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de la modification du mot de passe.');
                }
            }
        }

        return $this->render('profil/change_password.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/delete-account', name: 'app_profil_delete_account', methods: ['POST'])]
    public function deleteAccount(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non trouvé.');
        }

        // Vérification du token CSRF pour la sécurité
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_account', $submittedToken)) {
            $this->addFlash('error', 'Token de sécurité invalide. Veuillez réessayer.');
            return $this->redirectToRoute('app_profil_index');
        }

        // Récupérer les informations avant suppression pour le message
        $userEmail = $user->getEmail();
        $userName = $user->getFullName();

        try {
            // Démarrer une transaction pour garantir l'atomicité
            $entityManager->beginTransaction();

            // 1. Supprimer toutes les demandes de réinitialisation de mot de passe associées
            $resetPasswordRequests = $entityManager->getRepository(\App\Entity\ResetPasswordRequest::class)
                ->findBy(['user' => $user]);
            
            foreach ($resetPasswordRequests as $resetRequest) {
                $entityManager->remove($resetRequest);
            }

            // 2. Supprimer l'utilisateur
            $entityManager->remove($user);
            
            // 3. Appliquer toutes les suppressions
            $entityManager->flush();
            $entityManager->commit();

            // 4. Invalider le token de sécurité AVANT d'invalider la session
            $tokenStorage->setToken(null);
            
            // 5. Invalider complètement la session et en créer une nouvelle
            $session = $request->getSession();
            $session->invalidate();
            $session->migrate(true); // Force la création d'un nouveau session ID
            
            // 6. Ajouter le message flash dans la nouvelle session
            $this->addFlash('success', sprintf(
                'Le compte de %s (%s) a été supprimé avec succès. Nous sommes désolés de vous voir partir !',
                $userName ?: 'Utilisateur',
                $userEmail
            ));

            // 7. Redirection vers la page d'accueil avec une nouvelle requête propre
            return $this->redirectToRoute('app_home');

        } catch (\Exception $e) {
            // En cas d'erreur, annuler la transaction
            if ($entityManager->getConnection()->isTransactionActive()) {
                $entityManager->rollback();
            }
            
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression du compte. Veuillez contacter l\'administrateur.');
            return $this->redirectToRoute('app_profil_index');
        }
    }
}
