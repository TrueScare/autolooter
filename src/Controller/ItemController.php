<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\User;
use App\Form\ItemFormType;
use App\Repository\ItemRepository;
use App\Service\HeaderActionService;
use App\Service\PaginationService;
use App\Service\ProbabilityService;
use App\Struct\Order;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ItemController extends BaseController
{
    private ItemRepository $itemRepository;

    public function __construct(ItemRepository $itemRepository, PaginationService $paginationService, EntityManagerInterface $entityManager, LoggerInterface $logger, HeaderActionService $actionService)
    {
        parent::__construct($paginationService, $entityManager, $logger, $actionService);
        $this->itemRepository = $itemRepository;
    }

    #[Route('/item', name: 'item_index')]
    public function index(Request $request): Response
    {
        $order = Order::tryFrom($request->query->get('order'));
        $pageInfo = $this->paginationService->getPaginationInfoFromRequest($request);

        $items = $this->itemRepository->getItemsByOwner($this->getUser(), $pageInfo, $order);
        $maxItemsFound = $this->itemRepository->getItemsCountByOwner($this->getUser(), $pageInfo);

        return $this->render('/item/index.html.twig', [
            'items' => $items,
            'maxItemsFound' => $maxItemsFound,
            'page' => $pageInfo->getPage(),
            'pageSize' => $pageInfo->getPageSize(),
            'orderOptions' => [
                Order::NAME_ASC,
                Order::NAME_DESC,
                Order::RARITY_ASC,
                Order::RARITY_DESC
            ],
            'order' => $order,
            'searchTerm' => $pageInfo->getSearchTerm()
        ]);
    }

    #[Route('/item/edit/{id?}', name: 'item_edit')]
    public function detail(?Item $item, Request $request, TranslatorInterface $translator): Response
    {
        if ($item?->getOwner() !== $this->getUser()) {
            $this->redirectToRoute('app_home');
        }

        if (empty($item)) {
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
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Item $item */
            $item = $form->getData();
            $item->setOwner($this->getUser());

            try {
                $this->entityManager->persist($item);
                $this->entityManager->flush();
                $this->addFlash('success', $translator->trans('success.save'));
            } catch (\Exception $e) {
                $this->logger->error($e);
                $this->addFlash('danger', $translator->trans('error.save'));
            }
        }

        return $this->render('item/detail.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/item/new', name: 'item_new')]
    public function new(Request $request): RedirectResponse
    {
        return $this->redirectToRoute('item_edit', ['id' => null]);
    }

    #[Route('/item/random', name: 'item_random')]
    public function random(Request $request, ProbabilityService $probabilityService, TranslatorInterface $translator): Response
    {
        // needs to be set dynamically!
        $amount = 1;
        $probabilities = $probabilityService->getItemProbabilities($this->getUser());

        $picks = $this->pickMultipleFromItems($probabilities, $amount, $translator);

        $items = $this->itemRepository->getItemsById($this->getUser(),$picks);

        return $this->render('item/random.html.twig', [
            'items' => $items,
        ]);
    }

    private function pickMultipleFromItems(array $probabilityMapping, int $amount, TranslatorInterface $translator, bool $uniqueItems = false)
    {
        $keys = [];
        if(count($probabilityMapping) <= $amount && $uniqueItems){
            //only way to return unique items... we may not get the wanted amount though...
            $keys = array_keys($probabilityMapping);
            $this->addFlash('info', $translator->trans('item.random.notEnoughItems'));
            return $keys;

        } else if (count($probabilityMapping) > $amount && $uniqueItems){
            while(count($keys) < $amount){
                $id = $this->getPickFromItems($probabilityMapping);
                $keys[$id] = $this->getPickFromItems($probabilityMapping);
            }
        } else {
            while(count($keys) < $amount){
                $keys[] = $this->getPickFromItems($probabilityMapping);
            }
        }

        return $keys;
    }

    private function getPickFromItems(array $probabilityMapping)
    {
        // the items probability ALWAYS has to add up to 1 (100%)
        $luckyPick = rand(0, 100) / 100;

        foreach ($probabilityMapping as $item) {
            $luckyPick -= $item['individual_rarity'];
            if ($luckyPick <= 0) {
                return $item['id'];
            }
        }
        $this->addFlash('danger', "No item found... that's suspicious.");
        return end($probabilityMapping);
    }

    /**
     * @param Item $item
     * @param TranslatorInterface $translator
     * @return Response
     */
    #[Route('/item/delete/{id}', name: 'item_delete')]
    public function delete(Item $item, TranslatorInterface $translator): Response
    {
        if ($this->getUser() !== $item->getOwner()) {
            $this->addFlash('danger', $translator->trans('error.item.notfound'));
            return $this->redirectToRoute('item_index');
        }

        try {
            $this->entityManager->remove($item);
            $this->entityManager->flush();
            $this->addFlash('success', $translator->trans('success.delete'));
        } catch (\Exception $e) {
            $this->addFlash('danger', $translator->trans('error.delete'));
            $this->logger->error($e);
        }

        return $this->redirectToRoute('item_index');
    }

    #[Route('/item/pagination', name: 'item_pagination')]
    public function paginationGetItems(Request $request): Response
    {
        $order = Order::tryFrom($request->query->get('order'));
        $pageInfo = $this->paginationService->getPaginationInfoFromRequest($request);

        $items = $this->itemRepository->getItemsByOwner($this->getUser(), $pageInfo, $order);
        $maxItemsFound = $this->itemRepository->getItemsCountByOwner($this->getUser(), $pageInfo);

        return $this->json($this->render('components/listing_content.html.twig', [
            'entities' => $items,
            'maxItemsFound' => $maxItemsFound,
            'page' => $pageInfo->getPage(),
            'pageSize' => $pageInfo->getPageSize(),
            'orderOptions' => [
                Order::NAME_ASC,
                Order::NAME_DESC,
                Order::RARITY_ASC,
                Order::RARITY_DESC
            ],
            'order' => $order,
            'searchTerm' => $pageInfo->getSearchTerm(),
            'type' => 'item'
        ])
            ->getContent());
    }

    #[Route('/api/item/edit/{id?}', name: 'api_item_edit')]
    public function apiDetail(?Item $item, Request $request, TranslatorInterface $translator): JsonResponse
    {
        if ($item && $item->getOwner() !== $this->getUser()) {
            $this->addFlash('danger', $translator->trans('error.save'));
            return $this->json("", status: 403);
        }

        if (empty($item)) {
            $item = new Item();
        }

        /** @var User $owner */
        $owner = $this->getUser();

        $option = [
            'tableChoices' => $owner->getTables(),
            'rarityChoices' => $owner->getRarities(),
            'route' => $this->generateUrl('item_edit', ['id' => $item?->getId() ?? null])
        ];

        $form = $this->createForm(ItemFormType::class, $item, $option);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Item $item */
            $item = $form->getData();
            $item->setOwner($this->getUser());

            try {
                $this->entityManager->persist($item);
                $this->entityManager->flush();
                $this->addFlash('success', $translator->trans('success.save'));
                return $this->redirectToRoute('item_edit', ['id' => $item->getId()]);
            } catch (\Exception $e) {
                $this->logger->error($e);
                $this->addFlash('danger', $translator->trans('error.save'));
            }
        }

        return $this->json(
            $this->render('components/forms/form_basic.html.twig', [
                'item' => $item,
                'form' => $form->createView()
            ])->getContent()
        );
    }
}