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
        $owner = $this->getReference(UserFixture::BASE_USER);

        $common = new Rarity();
        $common->setValue($baseRarity);
        $common->setName("Common");
        $common->setColor("#6e6d6a");
        $common->setOwner($owner);
        $manager->persist($common);

        $rare = new Rarity();
        $rare->setValue($common->getValue() / 2);
        $rare->setName("Rare");
        $rare->setColor("#053880");
        $rare->setOwner($owner);
        $manager->persist($rare);

        $superrare = new Rarity();
        $superrare->setValue($rare->getValue() / 5);
        $superrare->setName("Superrare");
        $superrare->setColor("#2c1a52");
        $superrare->setOwner($owner);
        $manager->persist($superrare);

        $ultrarare = new Rarity();
        $ultrarare->setValue($superrare->getValue() / 10);
        $ultrarare->setName("Ultrarare");
        $ultrarare->setColor("#6b5601");
        $ultrarare->setOwner($owner);
        $manager->persist($ultrarare);

        $manager->flush();

        $this->addReference(self::RARITY_COMMON,$common);
        $this->addReference(self::RARITY_RARE, $rare);
        $this->addReference(self::RARITY_SUPERRARE, $superrare);
        $this->addReference(self::RARITY_ULTRARARE, $ultrarare);
    }
}
