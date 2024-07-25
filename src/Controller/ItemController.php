<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\User;
use App\Exceptions\NoItemFoundException;
use App\Form\ItemFormType;
use App\Form\RandomItemConfigType;
use App\Repository\ItemRepository;
use App\Service\HeaderActionService;
use App\Service\PaginationService;
use App\Service\ProbabilityService;
use App\Struct\Order;
use App\Struct\ProbabilityEntry;
use App\Struct\ProbabilityEntryCollection;
use App\Struct\RandomItemConfig;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Random\Randomizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ItemController extends EntityController
{
    #[Route('/item', name: 'item_index')]
    public function index(Request $request): Response
    {
        $order = Order::tryFrom($request->query->get('order'));
        $pageInfo = $this->paginationService->getPaginationInfoFromRequest($request);

        $items = $this->getEntityRepository()->getItemsByOwner($this->getUser(), $pageInfo, $order);
        $maxItemsFound = $this->getEntityRepository()->getItemsCountByOwner($this->getUser(), $pageInfo);

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
            'rarityChoices' => $owner->getRarities(),
        ];

        $form = $this->createForm(ItemFormType::class, $item, $option);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Item $item */
            $item = $form->getData();
            $item->setOwner($this->getUser());

            if (!empty($valueEnd = $item->getValueEnd()) && $valueEnd < $item->getValueStart()) {
                $item->setValueEnd($item->getValueStart());
                $item->setValueStart($valueEnd);
            }

            // "update" item and form
            $this->getEntityRepository()->findOneBy(['id' => $item->getId()]);
            $form = $this->createForm(ItemFormType::class, $item, $option);

            try {
                $this->entityManager->persist($item);
                $this->entityManager->flush();
                $this->addFlash('success', $translator->trans('save', domain: 'successes'));
            } catch (\Exception $e) {
                $this->logger->error($e);
                $this->addFlash('danger', $translator->trans('save', domain: 'errors'));
            }
            // we should have an id here :) haha nice
            return $this->redirectToRoute('item_edit', ['id' => $item->getId()]);
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

        $itemConfig = new RandomItemConfig();
        /** @var User $user */
        $user = $this->getUser();
        $options = [
            'tableChoices' => $user->getTables(),
            'rarityChoices' => $user->getRarities()
        ];
        $form = $this->createForm(RandomItemConfigType::class, $itemConfig, $options);
        $form->handleRequest($request);
        $items = [];
        $picks = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $ids = [];
            foreach ($itemConfig->getTables() as $table) {
                $ids[] = $table->getId();
            }

            $table_probabilities = $probabilityService->getTableProbabilities(
                $this->getUser(),
                $ids
            );

            $probabilities = $probabilityService->getItemProbabilities($this->getUser(), $table_probabilities, $itemConfig->getRarities());

            $probabilities = $probabilities->getFilteredResult(function (ProbabilityEntry $item) {
                return $item->getIndividualProbability() > 0;
            });

            try {
                $picks = $probabilityService->pickMultipleFromItems(
                    $probabilities,
                    $itemConfig->getAmount() > 0 ? $itemConfig->getAmount() : 1,
                    $itemConfig->isUniqueTables()
                );
            } catch (NoItemFoundException $e) {
                $this->addFlash('danger', $translator->trans('item.nonefound', domain: 'errors'));
                $picks = []; // make sure that the frontend can load
            }

            if(empty($picks) || count($picks) < $itemConfig->getAmount()) {
                $this->addFlash('info', $translator->trans('item.random.notEnoughItems'));
            }

            $items = $this->getEntityRepository()->getItemsById($this->getUser(), $picks);
            if (!$itemConfig->isUniqueTables()) {

                $quantityMapping = array_count_values($picks);

                $quantityItems = [];
                foreach ($items as $item) {
                    $count = $quantityMapping[$item->getId()];
                    foreach (range(1, $count) as $i) {
                        $quantityItems[] = $item;
                    }
                }
            }
        }

        return $this->render('item/random.html.twig', [
            'items' => $quantityItems ?? $items,
            'form' => $form->createView(),
        ]);
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
            $this->addFlash('danger', $translator->trans('item.notfound', domain: 'errors'));
            return $this->redirectToRoute('item_index');
        }

        try {
            $this->entityManager->remove($item);
            $this->entityManager->flush();
            $this->addFlash('success', $translator->trans('delete', domain: 'successes'));
        } catch (\Exception $e) {
            $this->addFlash('danger', $translator->trans('delete', domain: 'errors'));
            $this->logger->error($e);
        }

        return $this->redirectToRoute('item_index');
    }

    #[Route('/item/pagination', name: 'item_pagination')]
    public function paginationGetItems(Request $request): Response
    {
        $order = Order::tryFrom($request->query->get('order'));
        $pageInfo = $this->paginationService->getPaginationInfoFromRequest($request);

        $items = $this->getEntityRepository()->getItemsByOwner($this->getUser(), $pageInfo, $order);
        $maxItemsFound = $this->getEntityRepository()->getItemsCountByOwner($this->getUser(), $pageInfo);

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
            $this->addFlash('danger', $translator->trans('save', domain: 'errors'));
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
                $this->addFlash('success', $translator->trans('save', domain: 'successes'));
            } catch (\Exception $e) {
                $this->logger->error($e);
                $this->addFlash('danger', $translator->trans('save', domain: 'errors'));
            }
        }

        return $this->json(
            $this->render('components/forms/form_basic.html.twig', [
                'item' => $item,
                'form' => $form->createView()
            ])->getContent()
        );
    }

    protected function getControllerEntityClass(): string
    {
        return Item::class;
    }
}