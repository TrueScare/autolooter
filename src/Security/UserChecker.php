<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!($user instanceof User)) {
            throw new UnsupportedUserException("User is not of the right Type.");
        }

        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException("userchecker.unverified");
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
