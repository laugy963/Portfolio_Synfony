<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PrivacyController extends AbstractController
{
    #[Route('/politique-confidentialite', name: 'privacy_policy')]
    public function privacy(): Response
    {
        return $this->render('privacy_policy.html.twig');
    }
}
