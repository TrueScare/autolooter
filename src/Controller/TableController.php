<?php

namespace App\Controller;

use App\Entity\Rarity;
use App\Entity\Table;
use App\Entity\User;
use App\Form\RarityFormType;
use App\Form\TableFormType;
use App\Repository\ItemRepository;
use App\Repository\TableRepository;
use App\Service\OrderService;
use App\Service\PaginationService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TableController extends BaseController
{
    private TableRepository $tableRepository;

    public function __construct(TableRepository $tableRepository, PaginationService $paginationService, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        parent::__construct($paginationService,$entityManager,$logger);
        $this->tableRepository = $tableRepository;
    }

    #[Route('/table', name:"table_index")]
    public function index(Request $request){
        $order = $request->query->get('order');

        $pageInfo = $this->paginationService->getPaginationInfoFromRequest($request);
        $tables = $this->tableRepository->getTablesByOwner($this->getUser(), $pageInfo, $order);
        $maxItemsFound = $this->tableRepository->getTableCountByOwner($this->getUser());

        return $this->render('/table/index.html.twig', [
            'tables' => $tables,
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

    #[Route('/table/edit/{id?}', name: 'table_edit')]
    public function detail(?Table $table, Request $request): Response
    {
        if($table?->getOwner() !== $this->getUser())
        {
            $this->redirectToRoute('app_home');
        }

        /** @var User $owner */
        $owner = $this->getUser();

        $choices = $owner->getTables()->filter(function ($element) use ($table) {
            // do not be able to create circle references
            /** @var Table $element  */
            return empty($element->getCollectionRoot()[$table?->getId()]) && empty(($table?->getChildrenCollectionRecursive()[$element->getId()]));
        });

        $option = [
            'tableChoices' => $choices,
            'rarityChoices' => $owner->getRarities()
        ];

        $form = $this->createForm(TableFormType::class, $table,$option);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            /** @var Table $table */
            $table = $form->getData();
            $table->setOwner($this->getUser());

            try{
                $this->entityManager->persist($table);
                $this->entityManager->flush();
                $this->addFlash('success', 'Info: Speichern erfolgreich.');
            } catch(\Exception $e){
                $this->logger->error($e);
                $this->addFlash('error',"FEHLER: Speichern fehlgeschlagen.");
            }
        }

        return $this->render('table/detail.html.twig', [
            'table' => $table,
            'form' => $form
        ]);
    }

    #[Route('/table/new', name:'table_new')]
    public function new(): RedirectResponse
    {
        return $this->redirectToRoute('table_edit', ['id' => null]);
    }
}