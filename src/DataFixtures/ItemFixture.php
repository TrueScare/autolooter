<?php

namespace App\DataFixtures;

use App\Entity\Item;
use App\Entity\Table;
use App\Repository\TableRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
;

class ItemFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $rarityCollection = [
            $this->getReference(RarityFixture::RARITY_COMMON),
            $this->getReference(RarityFixture::RARITY_RARE),
            $this->getReference(RarityFixture::RARITY_SUPERRARE),
            $this->getReference(RarityFixture::RARITY_ULTRARARE),
        ];

        /** @var TableRepository $tableRepo */
        $tableRepo = $manager->getRepository(Table::class);

        $owner = $this->getReference(UserFixture::BASE_USER);
        $tables = $tableRepo->getAllTablesByOwner($owner);

        for($i = 0; $i < 120; $i++){
            $item = new Item();
            $item->setName('product' . $i);
            $item->setDescription('product description' . $i);
            $item->setOwner($owner);
            $item->setRarity($rarityCollection[array_rand($rarityCollection, 1)]);
            $item->setParent($tables[rand(0,count($tables)-1)]);
            $item->setValueStart(rand(1,10));
            $item->setValueEnd(rand(11,20));

            $manager->persist($item);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
      return [
          UserFixture::class,
          TableFixture::class,
          RarityFixture::class
      ];
    }
}
