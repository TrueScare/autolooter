<?php

namespace App\Controller;

use App\Entity\Rarity;
use App\Form\RarityFormType;
use App\Repository\RarityRepository;
use App\Service\PaginationService;
use App\Struct\Order;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RarityController extends BaseController
{
    private RarityRepository $rarityRepository;

    public function __construct(RarityRepository $rarityRepository, PaginationService $paginationService, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        parent::__construct($paginationService, $entityManager, $logger);
        $this->rarityRepository = $rarityRepository;
    }

    #[Route('/rarity', name: 'rarity_index')]
    public function index(Request $request): Response
    {
        $order = Order::tryFrom($request->query->get('order'));
        $pageInfo = $this->paginationService->getPaginationInfoFromRequest($request);

        $rarities = $this->rarityRepository->getRaritiesByOwner($this->getUser(), $pageInfo, $order);
        $maxItemsFound = $this->rarityRepository->getRarityCountByOwner($this->getUser(), $pageInfo);

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
            'headerActions' => $this->getHeaderActions(),
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
                $this->addFlash('success', $translator->trans('success.save'));
            } catch (\Exception $e) {
                $this->logger->error($e);
                $this->addFlash('danger', $translator->trans('error.save'));
            }
        }

        return $this->render('item/detail.html.twig', [
            'rarity' => $rarity,
            'form' => $form->createView(),
            'headerActions' => $this->getHeaderActions()
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
            $this->addFlash('danger', $translator->trans('error.rarity.notfound'));
            return $this->redirectToRoute('item_index');
        }

        try {
            $this->entityManager->remove($rarity);
            $this->entityManager->flush();
            $this->addFlash('success', $translator->trans('success.delete'));
        } catch (\Exception $e) {
            $this->addFlash('danger', $translator->trans('error.delete') . $e);
            $this->logger->error($e);
        }

        return $this->redirectToRoute('rarity_index');
    }

    #[Route('/rarity/pagination', name: 'rarity_pagination')]
    public function paginationGetRarities(Request $request): Response
    {
        $order = Order::tryFrom($request->query->get('order'));
        $pageInfo = $this->paginationService->getPaginationInfoFromRequest($request);

        $rarities = $this->rarityRepository->getRaritiesByOwner($this->getUser(), $pageInfo, $order);
        $maxItemsFound = $this->rarityRepository->getRarityCountByOwner($this->getUser(), $pageInfo);

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
            'headerActions' => $this->getHeaderActions(),
            'searchTerm' => $pageInfo->getSearchTerm(),
            'type' => 'rarity'
        ])->getContent()
        );
    }
}