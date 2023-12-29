<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\OrderService;
use App\Service\PaginationService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
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
    public function users(Request $request): Response
    {
        $order = $request->query->get('order');
        $pageInfo = $this->paginationService->getPaginationInfoFromRequest($request);
        $users = $this->userRepository->getUsers($pageInfo, $order);

        return $this->render('admin/users/index.html.twig',[
            'users' => $users,
            'maxItemsFound' => count($users),
            'orderOptions' => [
                OrderService::NAME_ASC => 'Username A-Z',
                OrderService::NAME_DESC => 'Username Z-A'
            ],
            'page' => $pageInfo->getPage(),
            'pageSize' => $pageInfo->getPageSize(),
            'order' => $order,
            'searchTerm' => $pageInfo->getSearchTerm()
        ]);
    }
}