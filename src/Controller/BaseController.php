<?php

namespace App\Controller;

use App\Service\PaginationService;
use App\Struct\HeaderAction;
use Doctrine\ORM\EntityManagerInterface;
use http\Header;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

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


    /**
     * @return HeaderAction[]
     */
    protected function getHeaderActions(): array
    {
        return [
            new HeaderAction(
                'Loot me up!',
                $this->generateUrl('item_random')
            ),
            new HeaderAction(
                'Zu den Raritäten',
                $this->generateUrl('rarity_index'),
                [
                    new HeaderAction(
                        'neue Rarität',
                        $this->generateUrl('rarity_new')
                    )
                ]
            ),
            new HeaderAction(
                'Zu den Tabellen',
                $this->generateUrl('table_index'),
                [
                    new HeaderAction(
                        'neue Tabelle',
                        $this->generateUrl('table_new')
                    )
                ]
            ),
            new HeaderAction(
                'Zu den Items',
                $this->generateUrl('item_index'),
                [
                    new HeaderAction(
                        'neues Item',
                        $this->generateUrl('item_new')
                    )
                ]
            )
        ];
    }


    /**
     * @return HeaderAction[]
     */
    protected function getAdminHeaderActions(): array
    {
        return [
            new HeaderAction(
                'Users',
                $this->generateUrl('admin_users')
            )
        ];
    }
}