<?php

namespace App\Tests\Entity;

use App\DataFixtures\RarityFixture;
use App\DataFixtures\UserFixture;
use App\Entity\Table;
use App\Entity\User;
use App\Repository\RarityRepository;
use App\Repository\TableRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TableTest extends KernelTestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $container = static::getContainer();
        $userRepo = $container->get(UserRepository::class);

        $this->user = $userRepo->findOneBy(['username' => UserFixture::BASE_USER]);
        $manager = $container->get(EntityManagerInterface::class);

        $table = $this->createTestTable();

        $manager->persist($table);
        $manager->flush();
    }

    public function testSearch()
    {
        $container = static::getContainer();
        $tableRepo = $container->get(TableRepository::class);
        $rarityRepository = $container->get(RarityRepository::class);
        $rarity = $rarityRepository->findOneBy(['name' => 'Rare']);
        $table = $tableRepo->findOneBy(['name' => 'Test Table']);

        $this->assertNotEmpty($table);
        $this->assertEquals('Test Table', $table->getName());
        $this->assertEquals($rarity, $table->getRarity());
        $this->assertEquals($this->user, $table->getOwner());
    }

    private function createTestTable(): Table
    {
        $container = static::getContainer();
        $rarityRepository = $container->get(RarityRepository::class);
        $rarity = $rarityRepository->findOneBy(['name' => 'Rare']);

        $table = new Table();
        $table->setOwner($this->user);
        $table->setRarity($rarity);
        $table->setName("Test Table");
        $table->setDescription("Test Table Description");

        return $table;
    }
}