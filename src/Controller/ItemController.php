<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class ItemController extends AbstractController
{
    private ItemRepository $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    #[Route('/item', name: 'item_index')]
    public function index()
    {
        $items = $this->itemRepository->findAll();
        return $this->render('/item/index.html.twig', [
            'items' => $items,
        ]);
    }
}