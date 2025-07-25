<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends EntityController
{
    #[Route('/user', name: 'user_edit_self')]
    public function userEdit(Request $request, TranslatorInterface $translator, UserPasswordHasherInterface $passwordHasher): RedirectResponse|Response
    {
        $user = $this->getUser();

        $form = $this->createForm(UserFormType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if (!empty($plainPassword = $form->get('plainPassword')->getData())) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $plainPassword)
                );
            }

            $user = $form->getData();

            try {
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $this->addFlash('success', $translator->trans('save', domain: 'successes'));
            } catch (\Exception $e) {
                $this->logger->error($e);
                $this->addFlash('danger', $translator->trans('save', domain: 'errors'));
            }
        }

        return $this->render('user/detail.html.twig', [
            'user' => $user,
            'form' => $form,
            'headerActions' => $this->getHeaderActions(),
        ]);
    }

    protected function getControllerEntityClass(): string
    {
        return User::class;
    }
}