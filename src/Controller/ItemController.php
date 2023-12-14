<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ItemController extends AbstractController
{
    private ItemRepository $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    #[Route('/item', name: 'item_index')]
    public function index(Request $request): Response
    {
        $page = $request->query->get('page');
        $pageLimit = $request->query->get('pageLimit');

        $items = $this->itemRepository->getItemsByOwner($this->getUser(),$page, $pageLimit);

        return $this->render('/item/index.html.twig', [
            'items' => $items,
            'page' => $page,
            'pageLimit' => $pageLimit
        ]);
    }
}