<?php

namespace App\Tests\Service;

use App\DataFixtures\UserFixture;
use App\Entity\User;
use App\Repository\ItemRepository;
use App\Repository\RarityRepository;
use App\Repository\UserRepository;
use App\Service\ProbabilityService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProbabailityServiceTest extends KernelTestCase
{
    /* When testing the probability of a table or item a precision of upto 8 decimals is sufficient. */
    protected static $decimalPresicion = 8;
    protected User $user;

    protected function setUp(): void
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
        $items = $service->pickMultipleFromItems($collection, 3, true);
        $this->assertNotEmpty($items);
        $this->assertCount(3, $items);
    }

    public function testItemFetchingFromSpecificTables()
    {
        $service = static::getContainer()->get(ProbabilityService::class);
        $tableMapping = $service->getTableProbabilities($this->user, [7]);
        $collection = $service->getItemProbabilities($this->user, $tableMapping);
        $items = $service->pickMultipleFromItems($collection);
        $this->assertNotEmpty($items);
        $this->assertCount(1, $items);

        $items = $service->pickMultipleFromItems($collection, 3, true);
        $this->assertCount(3, $items);
    }

    public function testItemFetchingWithoutMatch()
    {
        $service = static::getContainer()->get(ProbabilityService::class);
        $rarityRepo = static::getContainer()->get(RarityRepository::class);
        $rarity = $rarityRepo->findOneBy(['name' => 'Rare']);
        $tableMapping = $service->getTableProbabilities($this->user, [7]);
        $collection = $service->getItemProbabilities($this->user, $tableMapping, [$rarity]);

        $items = $collection->getFilteredResult(function ($item) {
            return $item->getIndividualProbability() > 0;
        });

        $this->assertCount(0, $items);
    }

    public function testProbabilityBySpecificSubtree()
    {
        $service = static::getContainer()->get(ProbabilityService::class);
        $itemRepo = static::getContainer()->get(ItemRepository::class);
        $item = $itemRepo->findOneBy(['name' => 'Item A']);

        $tableMapping = $service->getTableProbabilities($this->user, [2]);
        $collection = $service->getItemProbabilities($this->user, $tableMapping);
        $items = $collection->getFilteredResult(function ($item) {
            return $item->getIndividualProbability() > 0;
        });

        $this->assertNotEmpty($collection);
        $this->assertEquals(round((100 / 150) * 100 / 160, self::$decimalPresicion), round($items->getEntryByKey($item->getId())->getIndividualProbability(), self::$decimalPresicion));
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

        $this->assertEquals(round($manualProbability, self::$decimalPresicion), round($entry->getIndividualProbability(), self::$decimalPresicion));
    }
}
