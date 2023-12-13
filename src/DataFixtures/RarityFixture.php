<?php

namespace App\DataFixtures;

use App\Entity\Rarity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

;

class RarityFixture extends Fixture
{
    public const RARITY_COMMON = 'rarity_common';
    public const RARITY_RARE = 'rarity_rare';
    public const RARITY_SUPERRARE = 'rarity_superrare';
    public const RARITY_ULTRARARE = 'rarity_ultrarate';

    public function load(ObjectManager $manager): void
    {
        $baseRarity = 100;

        $common = new Rarity();
        $common->setValue($baseRarity);
        $manager->persist($common);

        $rare = new Rarity();
        $rare->setValue($common->getValue() / 2);
        $manager->persist($rare);

        $superrare = new Rarity();
        $superrare->setValue($rare->getValue() / 5);
        $manager->persist($superrare);

        $ultrarare = new Rarity();
        $ultrarare->setValue($superrare->getValue() / 10);
        $manager->persist($ultrarare);

        $manager->flush();

        $this->addReference(self::RARITY_COMMON,$common);
        $this->addReference(self::RARITY_RARE, $rare);
        $this->addReference(self::RARITY_SUPERRARE, $superrare);
        $this->addReference(self::RARITY_ULTRARARE, $ultrarare);
    }
}
