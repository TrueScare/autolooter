<?php

namespace App\Controller;

use App\Entity\Rarity;
use App\Form\RarityFormType;
use App\Repository\RarityRepository;
use App\Service\OrderService;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RarityController extends BaseController
{
    private RarityRepository $rarityRepository;

    public function __construct(RarityRepository $rarityRepository, PaginationService $paginationService, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        parent::__construct($paginationService,$entityManager,$logger);
        $this->rarityRepository = $rarityRepository;
    }

    #[Route('/rarity', name:'rarity_index')]
    public function index(Request $request){
        $order = $request->query->get('order');
        $pageInfo = $this->paginationService->getPaginationInfoFromRequest($request);

        $rarities = $this->rarityRepository->getRaritiesByOwner($this->getUser(), $pageInfo, $order);
        $maxItemsFound = $this->rarityRepository->getRarityCountByOwner($this->getUser());

        return $this->render('/rarity/index.html.twig', [
            'rarities' => $rarities,
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

    #[Route('/rarity/edit/{id?}', name: 'rarity_edit')]
    public function detail(?Rarity $rarity, Request $request): Response
    {
        if($rarity?->getOwner() !== $this->getUser())
        {
            $this->redirectToRoute('app_home');
        }

        if(empty($rarity)){
            $rarity = new Rarity();
        }

        $rarity->setOwner($this->getUser());

        $form = $this->createForm(RarityFormType::class, $rarity);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            /** @var Rarity $rarity */
            $rarity = $form->getData();

            try{
                $this->entityManager->persist($rarity);
                $this->entityManager->flush();
                $this->addFlash('success', 'Info: Speichern erfolgreich.');
            } catch(\Exception $e){
                $this->logger->error($e);
                $this->addFlash('error',"FEHLER: Speichern fehlgeschlagen.");
            }
        }

        return $this->render('item/detail.html.twig', [
            'rarity' => $rarity,
            'form' => $form,
            'headerActions' => $this->getHeaderActions()
        ]);
    }

    #[Route('/rarity/new', name:'rarity_new')]
    public function new(): RedirectResponse
    {
        return $this->redirectToRoute('rarity_edit', ['id' => null]);
    }
}