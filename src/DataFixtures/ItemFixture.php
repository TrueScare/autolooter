<?php

namespace App\DataFixtures;

use App\Entity\Item;
use Doctrine\Bundle\FixturesBundle\Fixture;
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

        $baseTable = $this->getReference(TableFixture::BASE_TABLE);
        $owner = $this->getReference(UserFixture::BASE_USER);

        for($i = 0; $i < 120; $i++){
            $item = new Item();
            $item->setName('product' . $i);
            $item->setDescription('product description' . $i);
            $item->setOwner($owner);
            $item->setRarity($rarityCollection[array_rand($rarityCollection, 1)]);
            $item->setParent($baseTable);
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
