<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\User;
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
                $this->addFlash('success', $translator->trans('save', domain: 'successes'));
            } catch (\Exception $e) {
                $this->logger->error($e);
                $this->addFlash('danger', $translator->trans('save', domain: 'errors'));
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

            $picks = $this->pickMultipleFromItems(
                $probabilities,
                $translator,
                $itemConfig->getAmount() > 0 ? $itemConfig->getAmount() : 1,
                $itemConfig->isUniqueTables()
            );

            $items = $this->getEntityRepository()->getItemsById($this->getUser(), $picks);
            if(!$itemConfig->isUniqueTables()) {

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
     * Produces an array of ids of items from the ProbabilityEntryCollection
     *
     * @param ProbabilityEntryCollection $probabilityMapping
     * @param TranslatorInterface $translator
     * @param int $amount
     * @param bool $uniqueItems
     * @return ProbabilityEntry|array|false
     */
    private function pickMultipleFromItems(ProbabilityEntryCollection $probabilityMapping, TranslatorInterface $translator, int $amount = 1, bool $uniqueItems = false)
    {
        $keys = [];
        if (count($probabilityMapping) <= $amount && $uniqueItems) {
            if (count($probabilityMapping) < $amount) {
                $this->addFlash('info', $translator->trans('item.random.notEnoughItems'));
            }
            //only way to return unique items... we may not get the wanted amount though...
            $keys = $probabilityMapping->getKeys();
        } else if (count($probabilityMapping) > $amount && $uniqueItems) {
            $keys = $this->getPickFromItemsUnique($probabilityMapping, $amount);
        } else {
            while (count($keys) < $amount) {
                $keys[] = $this->getPickFromItems($probabilityMapping);
            }
        }

        return $keys;
    }

    private function getPickFromItems(ProbabilityEntryCollection $probabilityMapping): ProbabilityEntry|false|int
    {
        // calculate random value in between 0 and total individual probability value
        $luckyPick = lcg_value()*(abs($probabilityMapping->getTotalProbability()));

        foreach ($probabilityMapping as $item) {
            $luckyPick -= $item->getIndividualProbability();
            if ($luckyPick <= 0) {
                return $item->getId();
            }
        }

        $this->addFlash('danger', "No item found... that's suspicious.");
        return $probabilityMapping->end()->getId();
    }

    private function getPickFromItemsUnique(ProbabilityEntryCollection $probabilityMapping, $amount)
    {
        $baseProbability = 1;
        $picks = [];

        for ($i = 0; $i < $amount; $i++) {

            $luckyPick = (mt_rand() / mt_getrandmax()) * $baseProbability;

            foreach ($probabilityMapping as $key => $item) {
                $luckyPick = $luckyPick - $item->getIndividualProbability();

                if ($luckyPick <= 0) {
                    $picks[$item->getId()] = $item->getId();
                    $baseProbability = $baseProbability - $item->getIndividualProbability();
                    $probabilityMapping->removeEntry($item);
                    break;
                }
            }
        }

        if (count($picks) <= 0) {
            $this->addFlash('danger', "No item found... that's suspicious.");
            return $probabilityMapping->end()->getId();
        }

        return $picks;
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