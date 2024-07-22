<?php

namespace App\DataFixtures;

use App\Entity\Table;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

;

class TableFixture extends Fixture implements DependentFixtureInterface
{

    public const TABLE1 = "table1";
    public const TABLE2 = "table2";
    public const TABLE3 = "table3";
    public const TABLE4 = "table4";
    public function load(ObjectManager $manager): void
    {
        $owner = $this->getReference(UserFixture::BASE_USER);

        $baseTable = new Table();
        $baseTable->setOwner($owner);
        $baseTable->setName("Base Table");
        $baseTable->setDescription("Very first Table of the whole tree");
        $baseTable->setRarity($this->getReference(RarityFixture::RARITY_COMMON));
        $manager->persist($baseTable);

        $layerOneOne = new Table();
        $layerOneOne->setOwner($owner);
        $layerOneOne->setName("Layer One 1");
        $layerOneOne->setDescription("First Table of the first layer");
        $layerOneOne->setRarity($this->getReference(RarityFixture::RARITY_RARE));
        $layerOneOne->setParent($baseTable);
        $manager->persist($layerOneOne);

        $layerOneTwo = new Table();
        $layerOneTwo->setOwner($owner);
        $layerOneTwo->setName("Layer One 2");
        $layerOneTwo->setDescription("First Table of the Second layer");
        $layerOneTwo->setRarity($this->getReference(RarityFixture::RARITY_COMMON));
        $layerOneTwo->setParent($baseTable);
        $manager->persist($layerOneTwo);

        $layerTwoOno = new Table();
        $layerTwoOno->setOwner($owner);
        $layerTwoOno->setName("Layer Two 1");
        $layerTwoOno->setDescription("Second Table of the Second layer");
        $layerTwoOno->setRarity($this->getReference(RarityFixture::RARITY_RARE));
        $layerTwoOno->setParent($layerOneOne);
        $manager->persist($layerTwoOno);

        $layerTwoTwo = new Table();
        $layerTwoTwo->setOwner($owner);
        $layerTwoTwo->setName("Layer Two 2");
        $layerTwoTwo->setDescription("Third Table of the Second layer");
        $layerTwoTwo->setRarity($this->getReference(RarityFixture::RARITY_COMMON));
        $layerTwoTwo->setParent($layerOneOne);
        $manager->persist($layerTwoTwo);

        $layerTwoThree = new Table();
        $layerTwoThree->setOwner($owner);
        $layerTwoThree->setName("Layer Two 3");
        $layerTwoThree->setDescription("Third Table of the Second layer");
        $layerTwoThree->setRarity($this->getReference(RarityFixture::RARITY_COMMON));
        $layerTwoThree->setParent($layerOneTwo);
        $manager->persist($layerTwoThree);

        $layerTwoFour = new Table();
        $layerTwoFour->setOwner($owner);
        $layerTwoFour->setName("Layer Two 4");
        $layerTwoFour->setDescription("Fourth Table of the Second layer");
        $layerTwoFour->setRarity($this->getReference(RarityFixture::RARITY_COMMON));
        $layerTwoFour->setParent($layerOneTwo);
        $manager->persist($layerTwoFour);

        $manager->flush();

        $this->addReference(self::TABLE1, $layerTwoOno);
        $this->addReference(self::TABLE2, $layerTwoTwo);
        $this->addReference(self::TABLE3, $layerTwoThree);
        $this->addReference(self::TABLE4, $layerTwoFour);
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
            RarityFixture::class
        ];
    }
}
