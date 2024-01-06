<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use App\Service\PaginationService;
use App\Struct\Order;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


class AdminController extends BaseController
{
    private UserRepository $userRepository;

    public function __construct(PaginationService      $paginationService,
                                EntityManagerInterface $entityManager,
                                LoggerInterface        $logger,
                                UserRepository         $userRepository)
    {
        parent::__construct($paginationService, $entityManager, $logger);
        $this->userRepository = $userRepository;
    }

    #[Route('/admin', name: "admin_index")]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig',[
            'headerActions' => $this->getAdminHeaderActions()
        ]);
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function users(Request $request): Response
    {
        $order = $request->query->get('order');
        $pageInfo = $this->paginationService->getPaginationInfoFromRequest($request);
        $users = $this->userRepository->getUsers($pageInfo, $order);

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
            'maxItemsFound' => count($users),
            'orderOptions' => [
                Order::NAME_ASC,
                Order::NAME_DESC
            ],
            'page' => $pageInfo->getPage(),
            'pageSize' => $pageInfo->getPageSize(),
            'order' => $order,
            'searchTerm' => $pageInfo->getSearchTerm(),
            'headerActions' => $this->getAdminHeaderActions()
        ]);
    }

    #[Route('/admin/user/{id?}', name:'admin_user_edit')]
    public function userEdit(?User $user, Request $request, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator): Response
    {
        if (empty($user)) {
            $user = new User();
        }

        $form = $this->createForm(UserFormType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if(!empty($plainPassword = $form->get('plainPassword')->getData())){
                $user->setPassword(
                  $passwordHasher->hashPassword($user, $plainPassword)
                );
            }

            $user = $form->getData();

            try {
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $this->addFlash('success', $translator->trans('success.save'));
            } catch (\Exception $e) {
                $this->logger->error($e);
                $this->addFlash('danger', $translator->trans('error.save'));
            }
        }

        return $this->render('admin/users/detail.html.twig', [
            'user' => $user,
            'form' => $form
        ]);
    }

    /**
     * @param User $user
     * @param TranslatorInterface $translator
     * @return RedirectResponse
     */
    #[Route('/admin/user/delete/{id}',name: 'admin_user_delete')]
    public function deleteUser(User $user, TranslatorInterface $translator): RedirectResponse
    {
        try {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            $this->addFlash('success', $translator->trans('success.delete'));
        } catch (\Exception $e){
            $this->addFlash('danger', $translator->trans('error.delete'));
            $this->logger->error($e);
        }

        return $this->redirectToRoute('admin_users');
    }
}