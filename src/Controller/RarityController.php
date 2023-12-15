<?php

namespace App\Controller;

use App\Repository\RarityRepository;
use App\Service\OrderService;
use App\Service\PaginationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RarityController extends BaseController
{
    private RarityRepository $rarityRepository;

    public function __construct(RarityRepository $rarityRepository, PaginationService $paginationService)
    {
        parent::__construct($paginationService);
        $this->rarityRepository = $rarityRepository;
    }

    #[Route('/rarity', name:'rarity_index')]
    public function index(Request $request){
        $order = $request->query->get('order');
        $pageInfo = $this->paginationService->getPaginationInfoFromRequest($request);

        $rarities = $this->rarityRepository->getRaritiesByOwner($this->getUser(), $pageInfo, $order);
        $maxItemsFound = $this->rarityRepository->getRarityCountByOwner($this->getUser());

        return $this->render('/rarity/index.html.twig', [
            'rarities' => $rarities,
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