<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class MessageController extends AbstractController
{
    #[Route('/api/messages/all', name:'api_messages_all')]
    public function getMessages(): JsonResponse
    {
        return $this->json(
            $this->render('components/messages/flash-message-binder.html.twig')->getContent()
        );
    }
}