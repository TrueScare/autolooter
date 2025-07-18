<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Rarity;
use App\Entity\Table;
use App\Entity\User;
use App\Form\MoveItemsBetweenRarities;
use App\Form\MoveTablesBetweenRarities;
use App\Form\RarityFormType;
use App\Repository\RarityRepository;
use App\Service\HeaderActionService;
use App\Service\PaginationService;
use App\Struct\Order;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RarityController extends EntityController
{
    #[Route('/rarity', name: 'rarity_index')]
    public function index(Request $request): Response
    {
        $order = Order::tryFrom($request->query->get('order'));
        $pageInfo = $this->paginationService->getPaginationInfoFromRequest($request);

        $rarities = $this->getEntityRepository()->getRaritiesByOwner($this->getUser(), $pageInfo, $order);
        $maxItemsFound = $this->getEntityRepository()->getRarityCountByOwner($this->getUser(), $pageInfo);

        return $this->render('/rarity/index.html.twig', [
            'rarities' => $rarities,
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

    #[Route('/rarity/edit/{id?}', name: 'rarity_edit')]
    public function detail(?Rarity $rarity, Request $request, TranslatorInterface $translator): Response
    {
        if ($rarity?->getOwner() !== $this->getUser()) {
            $this->redirectToRoute('app_home');
        }

        if (empty($rarity)) {
            $rarity = new Rarity();
        }

        $rarity->setOwner($this->getUser());

        $form = $this->createForm(RarityFormType::class, $rarity);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Rarity $rarity */
            $rarity = $form->getData();

            try {
                $this->entityManager->persist($rarity);
                $this->entityManager->flush();
                $this->addFlash('success', $translator->trans('save', domain: 'successes'));
                return $this->redirectToRoute('rarity_edit', ['id' => $rarity->getId()]);
            } catch (\Exception $e) {
                $this->logger->error($e);
                $this->addFlash('danger', $translator->trans('save', domain: 'errors'));
            }

            return $this->redirectToRoute('rarity_edit', [$rarity->getId()]);
        }

        return $this->render('rarity/detail.html.twig', [
            'rarity' => $rarity,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/rarity/new', name: 'rarity_new')]
    public function new(): RedirectResponse
    {
        return $this->redirectToRoute('rarity_edit', ['id' => null]);
    }

    /**
     * @param Rarity $rarity
     * @param TranslatorInterface $translator
     * @return RedirectResponse
     */
    #[Route('/rarity/delete/{id}', name: 'rarity_delete')]
    public function delete(Rarity $rarity, TranslatorInterface $translator): RedirectResponse
    {
        if ($this->getUser() !== $rarity->getOwner()) {
            $this->addFlash('danger', $translator->trans('rarity.notfound', domain: 'errors'));
            return $this->redirectToRoute('item_index');
        }

        try {
            $this->entityManager->remove($rarity);
            $this->entityManager->flush();
            $this->addFlash('success', $translator->trans('delete',domain: 'successes'));
        } catch (\Exception $e) {
            $this->addFlash('danger', $translator->trans('delete', domain: 'errors') . $e);
            $this->logger->error($e);
        }

        return $this->redirectToRoute('rarity_index');
    }

    #[Route('/rarity/pagination', name: 'rarity_pagination')]
    public function paginationGetRarities(Request $request): Response
    {
        $order = Order::tryFrom($request->query->get('order'));
        $pageInfo = $this->paginationService->getPaginationInfoFromRequest($request);

        $rarities = $this->getEntityRepository()->getRaritiesByOwner($this->getUser(), $pageInfo, $order);
        $maxItemsFound = $this->getEntityRepository()->getRarityCountByOwner($this->getUser(), $pageInfo);

        return $this->json($this->render('components/listing_content.html.twig', [
            'entities' => $rarities,
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
            'type' => 'rarity'
        ])->getContent()
        );
    }

    #[Route('/api/rarity/edit/{id?}', name: 'api_rarity_edit')]
    public function apiDetail(?Rarity $rarity, Request $request, TranslatorInterface $translator): Response
    {
        if ($rarity && $rarity->getOwner() !== $this->getUser()) {
            $this->addFlash('danger', $translator->trans('save', domain: 'errors'));
            return $this->json("", status: 403);
        }

        if (empty($rarity)) {
            $rarity = new Rarity();
        }

        $rarity->setOwner($this->getUser());

        $options = [
            'route' => $this->generateUrl('rarity_edit', ['id' => $rarity?->getId() ?? null])
        ];

        $form = $this->createForm(RarityFormType::class, $rarity, $options);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Rarity $rarity */
            $rarity = $form->getData();

            try {
                $this->entityManager->persist($rarity);
                $this->entityManager->flush();
                $this->addFlash('success', $translator->trans('save', domain: 'successes'));
            } catch (\Exception $e) {
                $this->logger->error($e);
                $this->addFlash('danger', $translator->trans('save', domain: 'errors'));
            }
        }

        return $this->json(
            $this->render('components/forms/form_basic.html.twig', [
                'rarity' => $rarity,
                'form' => $form->createView()
            ])->getContent()
        );
    }

    #[Route('/api/rarity/tables/from/{id}', name: 'api_rarity_move_tables')]
    public function moveTables(Request $request, Rarity $from, TranslatorInterface $translator)
    {
        if (!($this->getUser() === $from->getOwner())) {
            $this->addFlash('danger', $translator->trans('save', domain: 'errors'));
            return $this->json("", status: 403);
        }

        /** @var User $owner */
        $owner = $this->getUser();

        $choices = $owner->getRarities();

        $options = [
            'choices' => $choices,
            'route' => $this->generateUrl('api_rarity_move_tables', ['id' => $from->getId()])
        ];

        $form = $this->createForm(MoveTablesBetweenRarities::class, null, $options);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $to = $data['rarity'];

            $from->getTables()->map(function ($table) use ($to) {
                /** @var Table $table */
                $table->setRarity($to);
            });

            $from->getTables()->clear();

            try {
                $this->entityManager->persist($to);
                $this->entityManager->flush();

                $this->addFlash('success', $translator->trans('save', domain: 'successes'));

                return $this->json("");
            } catch (\Exception $e) {
                $this->logger->error($e);
                $this->addFlash('danger', $translator->trans('save', domain: 'errors'));
            }
        }

        return $this->json(
            $this->render('components/forms/rarity_move.html.twig', [
                'form' => $form->createView()
            ])
        );
    }

    #[Route('/api/rarity/items/from/{id}', name: 'api_rarity_move_items')]
    public function moveItems(Request $request, Rarity $from, TranslatorInterface $translator)
    {
        if (!($this->getUser() === $from->getOwner())) {
            $this->addFlash('danger', $translator->trans('save', domain: 'errors'));
            return $this->json("", status: 403);
        }

        /** @var User $owner */
        $owner = $this->getUser();

        $choices = $owner->getTables();

        $options = [
            'choices' => $choices,
            'route' => $this->generateUrl('api_rarity_move_items', ['id' => $from->getId()])
        ];

        $form = $this->createForm(MoveItemsBetweenRarities::class, null, $options);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // get table by table id from form
            $data = $form->getData();
            $to = $data['parent'];

            $from->getItems()->map(function ($item) use ($to) {
                /** @var Item $item */
                $item->setParent($to);
            });
            $from->getItems()->clear();

            try {
                $this->entityManager->persist($to);

                $this->entityManager->flush();

                $this->addFlash('success', $translator->trans('save', domain: 'successes'));
                return $this->json("");
            } catch (\Exception $e) {
                $this->logger->error($e);
                $this->addFlash('danger', $translator->trans('save', domain: 'errors'));
            }
        }

        return $this->json(
            $this->render('components/forms/rarity_move.html.twig', [
                'form' => $form->createView()
            ])
        );
    }

    protected function getControllerEntityClass(): string
    {
        return Rarity::class;
    }
}