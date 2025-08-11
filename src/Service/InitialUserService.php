<?php

namespace App\Service;

use App\Entity\Rarity;
use App\Entity\Table;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class InitialUserService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function provideDefaultsAfterRegistration(User $user): void
    {
        $rarity = $this->getDefaultRarity();
        $rarity->setOwner($user);

        $table = $this->getDefaultTable();
        $table->setRarity($rarity);
        $table->setOwner($user);

        $this->entityManager->persist($user);
        $this->entityManager->persist($rarity);
        $this->entityManager->persist($table);
        $this->entityManager->flush();
    }

    private function getDefaultRarity(): Rarity
    {
        $rarity = new Rarity();
        $rarity->setName("Default");
        $rarity->setDescription("Default rarity");
        $rarity->setValue(1);
        $rarity->setColor("#6e6d6a");

        return $rarity;
    }

    private function getDefaultTable(): Table
    {
        $table = new Table();
        $table->setName("Default");
        $table->setDescription("Default table");

        return $table;
    }
}
