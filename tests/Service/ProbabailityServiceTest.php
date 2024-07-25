<?php

namespace App\Tests\Service;

use App\DataFixtures\UserFixture;
use App\Entity\User;
use App\Repository\ItemRepository;
use App\Repository\UserRepository;
use App\Service\ProbabilityService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProbabailityServiceTest extends KernelTestCase
{
    protected User $user;

    protected function setUp():void
    {
        parent::setUp();
        $container = static::getContainer();
        $userRepo = $container->get(UserRepository::class);
        $this->user = $userRepo->findOneBy(['username' => UserFixture::BASE_USER]);
    }

    public function testItemFetching()
    {
        $service = static::getContainer()->get(ProbabilityService::class);

        $collection = $service->getItemProbabilities($this->user);
        $this->assertNotEmpty($collection);

        $items = $service->pickMultipleFromItems($collection);
        $this->assertNotEmpty($items);
    }

    public function testProbabilityCalculation()
    {
        $container = static::getContainer();
        $itemRepo = $container->get(ItemRepository::class);
        $item = $itemRepo->findOneBy(['name' => 'Item A']);
        $service = static::getContainer()->get(ProbabilityService::class);

        $collection = $service->getItemProbabilities($this->user);
        $entry = $collection->getEntryByKey($item->getId());
        $manualProbability = (pow((100 / 150), 2) * 100 / 160);

        $this->assertEquals($manualProbability,7, $entry->getIndividualProbability());
    }
}
