<?php

namespace App\Tests\Entity;

use App\DataFixtures\UserFixture;
use App\Entity\Rarity;
use App\Entity\User;
use App\Repository\RarityRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RarityTest extends KernelTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $container = static::getContainer();
        $userRepo = $container->get(UserRepository::class);

        $this->user = $userRepo->findOneBy(['username' => UserFixture::BASE_USER]);
        $manager = $container->get(EntityManagerInterface::class);

        $rarity = $this->createTestRarity();

        $manager->persist($rarity);
        $manager->flush();
    }

    public function testSearch(){
        $container = static::getContainer();
        $rarityRepo = $container->get(RarityRepository::class);
        $rarity = $rarityRepo->findOneBy(['name' => 'Test Rarity']);
        $this->assertNotNull($rarity);
        $this->assertEquals('Test Rarity Description', $rarity->getDescription());
        $this->assertEquals('15', $rarity->getValue());
        $this->assertEquals($this->user, $rarity->getOwner());
    }

    private function createTestRarity(): Rarity
    {
        $rarity = new Rarity();
        $rarity->setName('Test Rarity');
        $rarity->setDescription('Test Rarity Description');
        $rarity->setOwner($this->user);
        $rarity->setValue(15);
        $rarity->setColor('#ffffff');

        return $rarity;
    }
}