<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\User;
use App\Form\ItemFormType;
use App\Repository\ItemRepository;
use App\Service\OrderService;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ItemController extends BaseController
{
    private ItemRepository $itemRepository;

    public function __construct(ItemRepository $itemRepository, PaginationService $paginationService, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        parent::__construct($paginationService,$entityManager,$logger);
        $this->itemRepository = $itemRepository;
    }

    #[Route('/item', name: 'item_index')]
    public function index(Request $request): Response
    {
        $order = $request->query->get('order');
        $pageInfo = $this->paginationService->getPaginationInfoFromRequest($request);
        $items = $this->itemRepository->getItemsByOwner($this->getUser(), $pageInfo, $order);
        $maxItemsFound = $this->itemRepository->getItemsCountByOwner($this->getUser());

        return $this->render('/item/index.html.twig', [
            'items' => $items,
            'maxItemsFound' => $maxItemsFound,
            'page' => $pageInfo->getPage(),
            'pageSize' => $pageInfo->getPageSize(),
            'orderOptions' => [OrderService::NAME_ASC => 'Name A-Z'
                , OrderService::NAME_DESC => 'Name Z-A',
                OrderService::RARITY_ASC => 'Rarität aufsteigend',
                OrderService::RARITY_DESC => 'Rarität absteigend'],
            'order' => $order,
            'headerActions' => $this->getHeaderActions(),
            'searchTerm' => $pageInfo->getSearchTerm()
        ]);
    }

    #[Route('/item/edit/{id?}', name: 'item_edit')]
    public function detail(?Item $item, Request $request): Response
    {
        if($item?->getOwner() !== $this->getUser())
        {
            $this->redirectToRoute('app_home');
        }

        if(empty($item)){
            $item = new Item();
        }

        /** @var User $owner */
        $owner = $this->getUser();

        $option = [
            'tableChoices' => $owner->getTables(),
            'rarityChoices' => $owner->getRarities()
        ];

        $form = $this->createForm(ItemFormType::class, $item, $option);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            /** @var Item $item */
            $item = $form->getData();
            $item->setOwner($this->getUser());

            try{
                $this->entityManager->persist($item);
                $this->entityManager->flush();
                $this->addFlash('success', 'Info: Speichern erfolgreich.');
            } catch(\Exception $e){
                $this->logger->error($e);
                $this->addFlash('error',"FEHLER: Speichern fehlgeschlagen.");
            }
        }

        return $this->render('item/detail.html.twig', [
            'item' => $item,
            'form' => $form,
            'headerActions' => $this->getHeaderActions()
        ]);
    }

    #[Route('/item/new', name: 'item_new')]
    public function new(Request $request): RedirectResponse
    {
        return $this->redirectToRoute('item_edit', ['id' => null]);
    }

    #[Route('/item/random', name: 'item_random')]
    public function random(Request $request){
        /** @var User $owner */
        $owner = $this->getUser();

        $items = $owner->getItems();

        $pick = $this->getPickFromItems($items);

        return $this->render('item/random.html.twig',[
            'item' => $pick,
            'headerActions' => $this->getHeaderActions()
        ]);
    }

    private function getPickFromItems($items){
        // the items probability ALWAYS has to add up to 1 (100%)
        $luckyPick = rand(0, 100)/100;

        foreach($items as $item) {
            $luckyPick -= $item->getProbability();
            if($luckyPick <= 0){
                return $item;
            }
        }
        $this->addFlash('error', "No item found... that's suspicious.");
        return $items->last();
    }
}