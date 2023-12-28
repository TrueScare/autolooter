<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class AdminController extends BaseController
{
    private UserRepository $userRepository;

    public function __construct(PaginationService      $paginationService,
                                EntityManagerInterface $entityManager,
                                LoggerInterface        $logger,
                                UserRepository         $userRepository)
    {
        parent::__construct($paginationService, $entityManager, $logger);
        $this->userRepository = $userRepository;
    }

    #[Route('/admin', name:"admin_index")]
    public function index(): Response
    {
        return $this->render('admin/base.html.twig');
    }

    #[Route('/admin/users', name:'admin_users')]
    public function users(): Response
    {
        $users = $this->userRepository->findAll();
        return $this->render('admin/users/index.html.twig',[
            'users' => $users,
            'maxItemsFound' => count($users),
        ]);
    }
}