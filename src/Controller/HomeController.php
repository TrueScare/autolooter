<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends BaseController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
        ]);
    }

    #[Route('/home', name: 'user_home')]
    public function home(): Response
    {
        return $this->render('home/home.html.twig', [
        ]);
    }
}
