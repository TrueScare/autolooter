<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

;

class UserFixture extends Fixture
{
    public const BASE_USER= 'base_user';
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();

        $user->setUsername('base_user');
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                'base_user'
            )
        );

        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::BASE_USER, $user);
    }
}
