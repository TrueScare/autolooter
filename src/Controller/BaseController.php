<?php

namespace App\Controller;

use App\Service\HeaderActionService;
use App\Service\PaginationService;
use App\Struct\HeaderActionGroup;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/{_locale}')]
abstract class BaseController extends AbstractController
{
    protected EntityRepository $entityRepository;
    protected PaginationService $paginationService;
    protected EntityManagerInterface $entityManager;
    protected LoggerInterface $logger;
    private HeaderActionService $actionService;

    public function __construct(PaginationService      $paginationService,
                                EntityManagerInterface $entityManager,
                                LoggerInterface        $logger,
                                HeaderActionService    $actionService)
    {
        $this->paginationService = $paginationService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->actionService = $actionService;
    }

    /**
     * @return HeaderActionGroup
     */
    protected function getDefaultHeaderActions(): HeaderActionGroup
    {
        return $this->actionService->getDefaultHeaderActions();
    }

    /**
     * @return HeaderActionGroup
     */
    protected function getHeaderActions(): HeaderActionGroup
    {
        return $this->actionService->getUserHeadeActions();
    }

    /**
     * @return HeaderActionGroup
     */
    protected function getAdminHeaderActions(): HeaderActionGroup
    {
        return $this->actionService->getAdminHeaderActions();
    }

    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        if (empty($parameters['headerActions'])) {
            if ($this->getUser()) {
                $parameters['headerActions'] = $this->getHeaderActions();
            } else {
                $parameters['headerActions'] = $this->getDefaultHeaderActions();
            }
        }
        return parent::render($view, $parameters, $response);
    }
}