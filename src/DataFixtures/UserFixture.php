<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Struct\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

;

class UserFixture extends Fixture
{
    public const BASE_USER = 'base_user';
    public const ADMIN_USER = 'admin_user';
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();

        $user->setUsername('base_user');
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                'base_user',
            )
        );
        $user->setEmail('user@testmail.de');

        $admin = new User();

        $admin->setUsername('admin_user');
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin,
                'admin_user')
        );
        $admin->setRoles([Role::ADMIN]);
        $admin->setEmail('admin@testmail.de');

        $manager->persist($user);
        $manager->persist($admin);
        $manager->flush();

        $this->addReference(self::BASE_USER, $user);
    }
}
