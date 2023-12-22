<?php

namespace App\Controller;

use App\Service\PaginationService;
use App\Struct\HeaderAction;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{
    protected PaginationService $paginationService;
    protected EntityManagerInterface $entityManager;
    protected LoggerInterface $logger;

    public function __construct(PaginationService $paginationService, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->paginationService = $paginationService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    protected function getHeaderActions(){
        return [
            new HeaderAction(
                'Loot me up!',
                $this->generateUrl('item_random')
            ),
            new HeaderAction(
                'Zu den Raritäten',
                $this->generateUrl('rarity_index')
            ),
            new HeaderAction(
                'Zu den Tabellen',
                $this->generateUrl('table_index')
            ),
            new HeaderAction(
                'Zu den Items',
                $this->generateUrl('item_index')
            )
        ];
    }
}