<?php

namespace App\Tests\Entity;

use App\DataFixtures\UserFixture;
use App\Entity\Item;
use App\Entity\User;
use App\Repository\ItemRepository;
use App\Repository\RarityRepository;
use App\Repository\TableRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ItemTest extends KernelTestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $container = static::getContainer();
        $userRepo = $container->get(UserRepository::class);

        $this->user = $userRepo->findOneBy(['username' => UserFixture::BASE_USER]);
        $manager = $container->get(EntityManagerInterface::class);

        $table = $this->createTestItem();

        $manager->persist($table);
        $manager->flush();
    }

    public function testSearch()
    {
        $container = static::getContainer();
        $itemRepo = $container->get(ItemRepository::class);
        $rarityRepo = $container->get(RarityRepository::class);
        $tableRepo = $container->get(TableRepository::class);

        $item = $itemRepo->findOneBy(['name' => 'Test Item']);
        $rarity = $rarityRepo->findOneBy(['name' => 'Rare']);
        $table = $tableRepo->findOneBy(['name' => 'Base Table']);

        $this->assertNotNull($item);
        $this->assertEquals('Test Item', $item->getName());
        $this->assertEquals($this->user, $item->getOwner());
        $this->assertEquals($rarity, $item->getRarity());
        $this->assertEquals($table, $item->getParent());
        $this->assertNull($item->getValueEnd());
    }


    private function createTestItem()
    {
        $container = static::getContainer();
        $rarityRepo = $container->get(RarityRepository::class);
        $tableRepo = $container->get(TableRepository::class);

        $rarity = $rarityRepo->findOneBy(['name' => 'Rare']);
        $table = $tableRepo->findOneBy(['name' => 'Base Table']);

        $item = new Item();
        $item->setRarity($rarity);
        $item->setParent($table);
        $item->setOwner($this->user);
        $item->setName("Test Item");
        $item->setDescription("Test Item Description");
        $item->setValueStart(16);

        return $item;
    }
}