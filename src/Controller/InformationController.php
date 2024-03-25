<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;


class InformationController extends AbstractController
{
    #[Route('/information/impressum', name: 'information_impressum')]
    public function impressum(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render(
            'information/impressum.html.twig'
        );
    }
}