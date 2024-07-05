<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Table;
use App\Entity\User;
use App\Form\MoveItemsBetweenTablesType;
use App\Form\MoveTablesBetweenTablesType;
use App\Form\TableFormType;
use App\Struct\Order;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TableController extends EntityController
{
    #[Route('/table', name: "table_index")]
    public function index(Request $request): Response
    {
        $order = Order::tryFrom($request->query->get('order'));
        $pageInfo = $this->paginationService->getPaginationInfoFromRequest($request);

        $tables = $this->getEntityRepository()->getTablesByOwner($this->getUser(), $pageInfo, $order);
        $maxItemsFound = $this->getEntityRepository()->getTableCountByOwner($this->getUser(), $pageInfo);

        return $this->render('/table/index.html.twig', [
            'tables' => $tables,
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

    #[Route('/table/edit/{id?}', name: 'table_edit')]
    public function detail(?Table $table, Request $request, TranslatorInterface $translator): Response
    {
        if ($table?->getOwner() !== $this->getUser()) {
            $this->redirectToRoute('app_home');
        }

        if (empty($table)) {
            $table = new Table();
        }

        /** @var User $owner */
        $owner = $this->getUser();

        $table->setOwner($owner);

        $choices = $owner->getTables()->filter(function ($element) use ($table) {
            // do not be able to create circle references
            /** @var Table $element */
            return empty(($table?->getChildrenCollectionRecursive()[$element->getId()]));
        });

        $option = [
            'tableChoices' => $choices,
            'rarityChoices' => $owner->getRarities()
        ];

        $form = $this->createForm(TableFormType::class, $table, $option);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Table $table */
            $table = $form->getData();

            try {
                $this->entityManager->persist($table);
                $this->entityManager->flush();
                $this->addFlash('success', $translator->trans('save', domain: 'successes'));
                return $this->redirectToRoute('table_edit', ['id' => $table->getId()]);
            } catch (\Exception $e) {
                $this->logger->error($e);
                $this->addFlash('danger', $translator->trans('save', domain: 'errors'));
            }

            return $this->redirectToRoute('table_edit', ['id' => $table->getId()]);
        }

        return $this->render('table/detail.html.twig', [
            'table' => $table,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/table/new', name: 'table_new')]
    public function new(): RedirectResponse
    {
        return $this->redirectToRoute('table_edit', ['id' => null]);
    }

    /**
     * @param Table $table
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return RedirectResponse
     */
    #[Route('/table/delete/{id}', name: 'table_delete')]
    public function delete(Table $table, Request $request, TranslatorInterface $translator): RedirectResponse
    {
        if ($this->getUser() !== $table->getOwner()) {
            $this->addFlash('danger', $translator->trans('table.notfound', domain: 'errors'));
            return $this->redirectToRoute('user_home');
        }

        try {
            $this->entityManager->remove($table);
            $this->entityManager->flush();
            $this->addFlash('success', $translator->trans('delete', domain: 'successes'));
        } catch (\Exception $e) {
            $this->addFlash('danger', $translator->trans('delete', domain: 'errors'));
            $this->logger->error($e);
        }

        return $this->redirectToRoute('table_index');
    }

    #[Route('/table/pagination', name: "table_pagination")]
    public function paginationGetTables(Request $request): Response
    {
        $order = Order::tryFrom($request->query->get('order'));
        $pageInfo = $this->paginationService->getPaginationInfoFromRequest($request);

        $tables = $this->getEntityRepository()->getTablesByOwner($this->getUser(), $pageInfo, $order);
        $maxItemsFound = $this->getEntityRepository()->getTableCountByOwner($this->getUser(), $pageInfo);

        return $this->json($this->render('components/listing_content.html.twig', [
            'entities' => $tables,
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
            'type' => 'table'
        ])->getContent()
        );
    }

    #[Route('/api/table/edit/{id?}', name: 'api_table_edit')]
    public function apiDetail(?Table $table, Request $request, TranslatorInterface $translator): Response
    {
        if ($table && $table->getOwner() !== $this->getUser()) {
            $this->addFlash('danger', $translator->trans('save', domain: 'errors'));
            return $this->json("", status: 403);
        }

        if (empty($table)) {
            $table = new Table();
        }

        /** @var User $owner */
        $owner = $this->getUser();

        $table->setOwner($owner);

        $choices = $owner->getTables()->filter(function ($element) use ($table) {
            // do not be able to create circle references
            /** @var Table $element */
            return empty(($table?->getChildrenCollectionRecursive()[$element->getId()]));
        });

        $option = [
            'tableChoices' => $choices,
            'rarityChoices' => $owner->getRarities(),
            'route' => $this->generateUrl('table_edit', ['id' => $table?->getId() ?? null])
        ];

        $form = $this->createForm(TableFormType::class, $table, $option);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Table $table */
            $table = $form->getData();

            try {
                $this->entityManager->persist($table);
                $this->entityManager->flush();
                $this->addFlash('success', $translator->trans('save', domain: 'successes'));
            } catch (\Exception $e) {
                $this->logger->error($e);
                $this->addFlash('danger', $translator->trans('save', domain: 'errors'));
            }
        }

        return $this->json(
            $this->render('components/forms/form_basic.html.twig', [
                'table' => $table,
                'form' => $form->createView()
            ])->getContent()
        );
    }

    #[Route('/api/table/items/from/{id}', name: 'api_table_move_items', methods: 'POST')]
    public function moveItemsBetweenTables(Request $request, Table $from, TranslatorInterface $translator): \Symfony\Component\HttpFoundation\JsonResponse
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
            'route' => $this->generateUrl('api_table_move_items', ['id' => $from->getId()])
        ];

        $form = $this->createForm(MoveItemsBetweenTablesType::class, null, $options);

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
            $this->render('components/forms/move_item_form.html.twig', [
                'form' => $form->createView(),
            ])->getContent()
        );
    }

    #[Route('/api/table/tables/from/{id}', name: 'api_table_move_tables', methods: 'POST')]
    public function moveTablesBetweenTables(Request $request, Table $from, TranslatorInterface $translator)
    {
        if (!($this->getUser() === $from->getOwner())) {
            $this->addFlash('danger', $translator->trans('save', domain: 'errors'));
            return $this->json("", status: 403);
        }

        /** @var User $owner */
        $owner = $this->getUser();

        $choices = $owner->getTables()->filter(function ($element) use ($from) {
            // do not be able to create circle references
            /** @var Table $element */
            return empty(($from->getChildrenCollectionRecursive()[$element->getId()]));
        });

        $options = [
            'choices' => $choices,
            'route' => $this->generateUrl('api_table_move_tables', ['id' => $from->getId()])
        ];

        $form = $this->createForm(MoveTablesBetweenTablesType::class, null, $options);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $to = $data['parent'];

            $from->getTables()->map(function ($table) use ($to) {
                /** @var Table $table */
                $table->setParent($to);
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
            $this->render('components/forms/move_table_form.html.twig', [
                'form' => $form->createView()
            ])->getContent()
        );
    }

    protected function getControllerEntityClass(): string
    {
        return Table::class;
    }
}