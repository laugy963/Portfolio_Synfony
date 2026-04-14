<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\VerificationCodeType;
use App\Service\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    private const RESEND_COOLDOWN_SECONDS = 60;

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager,
        EmailVerificationService $emailVerificationService
    ): Response {
        // Rediriger si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validation supplémentaire côté serveur
            $missingFields = [];
            
            if (empty(trim($user->getFirstName()))) {
                $missingFields[] = 'prénom';
            }
            
            if (empty(trim($user->getLastName()))) {
                $missingFields[] = 'nom';
            }
            
            if (empty(trim($user->getEmail()))) {
                $missingFields[] = 'email';
            }
            
            if (empty(trim($form->get('plainPassword')->getData()))) {
                $missingFields[] = 'mot de passe';
            }
            
            if (!empty($missingFields)) {
                $this->addFlash('error', 'Veuillez remplir tous les champs requis : ' . implode(', ', $missingFields) . '.');
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }
            
            // Vérifier si l'email existe déjà
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            
            if ($existingUser) {
                $this->addFlash('error', 'Un compte avec cet email existe déjà.');
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }

            // Définir le rôle utilisateur par défaut
            $user->setRoles(['ROLE_USER']);
            
            // Définir la date de création
            $user->setCreatedAt(new \DateTimeImmutable());
            
            // L'utilisateur n'est pas vérifié par défaut
            $user->setIsVerified(false);
            
            // Encoder le mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Sauvegarder en base
            $entityManager->persist($user);
            $entityManager->flush();

            // Envoyer l'email avec le code de vérification
            try {
                $emailVerificationService->sendEmailConfirmation($user);
                
                // Stocker l'email en session pour la page de vérification
                $request->getSession()->set('verification_email', $user->getEmail());
                $request->getSession()->set('verification_code_last_sent_at', time());
                
                $this->addFlash('success', 'Votre compte a été créé ! Un code de vérification a été envoyé à votre adresse email.');
                
                return $this->redirectToRoute('app_verify_code');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Le compte a été créé mais l\'email de vérification n\'a pas pu être envoyé. Contactez l\'administrateur.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/code', name: 'app_verify_code')]
    public function verifyCode(Request $request, EmailVerificationService $emailVerificationService): Response
    {
        // Récupérer l'email depuis la session
        $email = $request->getSession()->get('verification_email');
        
        if (!$email) {
            $this->addFlash('error', 'Session expirée. Veuillez vous inscrire à nouveau.');
            return $this->redirectToRoute('app_register');
        }

        $form = $this->createForm(VerificationCodeType::class, [
            'email' => $email
        ], [
            'readonly_email' => true
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $verificationCode = $data['code'];
            
            if ($emailVerificationService->verifyUserEmailWithCode($email, $verificationCode)) {
                // Supprimer l'email de la session
                $request->getSession()->remove('verification_email');
                
                $this->addFlash('success', '🎉 Votre email a été vérifié avec succès ! Vous pouvez maintenant vous connecter.');
                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('error', 'Code de vérification invalide ou expiré. Veuillez vérifier le code ou demander un nouveau code.');
            }
        }

        return $this->render('registration/verify_code.html.twig', [
            'verificationForm' => $form,
            'email' => $email,
        ]);
    }

    #[Route('/verify/resend', name: 'app_resend_code', methods: ['POST'])]
    public function resendCode(Request $request, EmailVerificationService $emailVerificationService, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('resend_verification_code', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton de securite invalide. Veuillez reessayer.');

            return $this->redirectToRoute('app_verify_code');
        }

        $email = $request->getSession()->get('verification_email');
        
        if (!$email) {
            $this->addFlash('error', 'Session expirée. Veuillez vous inscrire à nouveau.');
            return $this->redirectToRoute('app_register');
        }

        $lastSentAt = (int) $request->getSession()->get('verification_code_last_sent_at', 0);
        $secondsRemaining = self::RESEND_COOLDOWN_SECONDS - (time() - $lastSentAt);

        if ($secondsRemaining > 0) {
            $this->addFlash('warning', sprintf('Veuillez patienter %d seconde(s) avant de demander un nouveau code.', $secondsRemaining));

            return $this->redirectToRoute('app_verify_code');
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        
        if ($user && !$user->isVerified()) {
            try {
                $emailVerificationService->resendVerificationCode($user);
                $request->getSession()->set('verification_code_last_sent_at', time());
                $this->addFlash('success', 'Un nouveau code de vérification a été envoyé à votre adresse email.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Impossible d\'envoyer le code. Veuillez réessayer plus tard.');
            }
        } else {
            $this->addFlash('error', 'Utilisateur non trouvé ou déjà vérifié.');
        }

        return $this->redirectToRoute('app_verify_code');
    }
}
