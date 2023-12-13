<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
;

class UserFixture extends Fixture
{
    public const BASE_USER= 'base_user';

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUsername('base_user');
        $user->setPassword('base_user');

        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::BASE_USER, $user);
    }
}
