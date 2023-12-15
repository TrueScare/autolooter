<?php

namespace App\Controller;

use App\Service\PaginationService;
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
}