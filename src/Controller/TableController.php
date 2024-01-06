<?php

namespace App\Controller;

use App\Entity\Table;
use App\Entity\User;
use App\Form\TableFormType;
use App\Repository\TableRepository;
use App\Service\PaginationService;
use App\Struct\Order;
use Doctrine\ORM\EntityManagerInterface;
use mysql_xdevapi\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TableController extends BaseController
{
    private TableRepository $tableRepository;

    public function __construct(TableRepository $tableRepository, PaginationService $paginationService, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        parent::__construct($paginationService, $entityManager, $logger);
        $this->tableRepository = $tableRepository;
    }

    #[Route('/table', name: "table_index")]
    public function index(Request $request): Response
    {
        $order = Order::tryFrom($request->query->get('order'));
        $pageInfo = $this->paginationService->getPaginationInfoFromRequest($request);

        $tables = $this->tableRepository->getTablesByOwner($this->getUser(), $pageInfo, $order);
        $maxItemsFound = $this->tableRepository->getTableCountByOwner($this->getUser(), $pageInfo);

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
            'headerActions' => $this->getHeaderActions(),
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
                $this->addFlash('success', $translator->trans('success.save'));
            } catch (\Exception $e) {
                $this->logger->error($e);
                $this->addFlash('danger', $translator->trans('error.save'));
            }
        }

        return $this->render('table/detail.html.twig', [
            'table' => $table,
            'form' => $form->createView(),
            'headerActions' => $this->getHeaderActions()
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
            $this->addFlash('danger', $translator->trans('error.table.notfound'));
            return $this->redirectToRoute('user_home');
        }

        try {
            $this->entityManager->remove($table);
            $this->entityManager->flush();
            $this->addFlash('success', $translator->trans('success.delete'));
        } catch (\Exception $e){
            $this->addFlash('danger', $translator->trans('error.delete'));
            $this->logger->error($e);
        }

        return $this->redirectToRoute('table_index');
    }
}