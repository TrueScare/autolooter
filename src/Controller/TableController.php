<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use App\Repository\TableRepository;
use App\Service\OrderService;
use App\Service\PaginationService;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TableController extends AbstractController
{
    private TableRepository $tableRepository;
    private PaginationService $paginationService;

    public function __construct(TableRepository $tableRepository, PaginationService $paginationService)
    {
        $this->tableRepository = $tableRepository;
        $this->paginationService = $paginationService;
    }

    #[Route('/table', name:"table_index")]
    public function index(Request $request){
        $order = $request->query->get('order');

        $pageInfo = $this->paginationService->getPaginationInfoFromRequest($request);
        $tables = $this->tableRepository->getTablesByOwner($this->getUser(), $pageInfo, $order);
        $maxItemsFound = $this->tableRepository->getTableCountByOwner($this->getUser());

        return $this->render('/table/index.html.twig', [
            'tables' => $tables,
            'maxItemsFound' => $maxItemsFound,
            'page' => $pageInfo->getPage(),
            'pageSize' => $pageInfo->getPageSize(),
            'orderOptions' => [OrderService::NAME_ASC => 'Name A-Z'
                , OrderService::NAME_DESC => 'Name Z-A',
                OrderService::RARITY_ASC => 'Rarität aufsteigend',
                OrderService::RARITY_DESC => 'Rarität absteigend'],
            'order' => $order
        ]);
    }
}