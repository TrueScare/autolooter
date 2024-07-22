<?php

namespace App\DataFixtures;

use App\Entity\Item;
use App\Entity\Table;
use App\Repository\TableRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

;

class ItemFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $owner = $this->getReference(UserFixture::BASE_USER);

        $itemA = new Item();
        $itemA->setOwner($owner);
        $itemA->setParent($this->getReference(TableFixture::TABLE1));
        $itemA->setRarity($this->getReference(RarityFixture::RARITY_COMMON));
        $itemA->setName("Item A");
        $itemA->setDescription("Item A description");
        $itemA->setValueStart(15);
        $manager->persist($itemA);

        $itemB = new Item();
        $itemB->setOwner($owner);
        $itemB->setParent($this->getReference(TableFixture::TABLE1));
        $itemB->setRarity($this->getReference(RarityFixture::RARITY_RARE));
        $itemB->setName("Item B");
        $itemB->setDescription("Item B description");
        $itemB->setValueStart(10);
        $manager->persist($itemB);

        $itemC = new Item();
        $itemC->setOwner($owner);
        $itemC->setParent($this->getReference(TableFixture::TABLE1));
        $itemC->setRarity($this->getReference(RarityFixture::RARITY_SUPERRARE));
        $itemC->setName("Item C");
        $itemC->setDescription("Item C description");
        $itemC->setValueStart(120);
        $manager->persist($itemC);

        $itemD = new Item();
        $itemD->setOwner($owner);
        $itemD->setParent($this->getReference(TableFixture::TABLE2));
        $itemD->setRarity($this->getReference(RarityFixture::RARITY_COMMON));
        $itemD->setName("Item D");
        $itemD->setDescription("Item D description");
        $itemD->setValueStart(150);
        $manager->persist($itemD);

        $itemE = new Item();
        $itemE->setOwner($owner);
        $itemE->setParent($this->getReference(TableFixture::TABLE2));
        $itemE->setRarity($this->getReference(RarityFixture::RARITY_COMMON));
        $itemE->setName("Item E");
        $itemE->setDescription("Item E description");
        $itemE->setValueStart(151);
        $manager->persist($itemE);

        $itemF = new Item();
        $itemF->setOwner($owner);
        $itemF->setParent($this->getReference(TableFixture::TABLE2));
        $itemF->setRarity($this->getReference(RarityFixture::RARITY_ULTRARARE));
        $itemF->setName("Item F");
        $itemF->setDescription("Item F description");
        $itemF->setValueStart(1511);
        $manager->persist($itemF);

        $itemG = new Item();
        $itemG->setOwner($owner);
        $itemG->setParent($this->getReference(TableFixture::TABLE3));
        $itemG->setRarity($this->getReference(RarityFixture::RARITY_COMMON));
        $itemG->setName("Item G");
        $itemG->setDescription("Item G description");
        $itemG->setValueStart(161);
        $manager->persist($itemG);

        $itemH = new Item();
        $itemH->setOwner($owner);
        $itemH->setParent($this->getReference(TableFixture::TABLE3));
        $itemH->setRarity($this->getReference(RarityFixture::RARITY_RARE));
        $itemH->setName("Item H");
        $itemH->setDescription("Item H description");
        $itemH->setValueStart(164);
        $manager->persist($itemH);

        $itemI = new Item();
        $itemI->setOwner($owner);
        $itemI->setParent($this->getReference(TableFixture::TABLE3));
        $itemI->setRarity($this->getReference(RarityFixture::RARITY_SUPERRARE));
        $itemI->setName("Item I");
        $itemI->setDescription("Item I description");
        $itemI->setValueStart(15);
        $manager->persist($itemI);

        $itemJ = new Item();
        $itemJ->setOwner($owner);
        $itemJ->setParent($this->getReference(TableFixture::TABLE4));
        $itemJ->setRarity($this->getReference(RarityFixture::RARITY_COMMON));
        $itemJ->setName("Item J");
        $itemJ->setDescription("Item J description");
        $itemJ->setValueStart(15);
        $manager->persist($itemJ);

        $itemK = new Item();
        $itemK->setOwner($owner);
        $itemK->setParent($this->getReference(TableFixture::TABLE4));
        $itemK->setRarity($this->getReference(RarityFixture::RARITY_COMMON));
        $itemK->setName("Item K");
        $itemK->setDescription("Item K description");
        $itemK->setValueStart(151);
        $manager->persist($itemK);

        $itemL = new Item();
        $itemL->setOwner($owner);
        $itemL->setParent($this->getReference(TableFixture::TABLE4));
        $itemL->setRarity($this->getReference(RarityFixture::RARITY_ULTRARARE));
        $itemL->setName("Item L");
        $itemL->setDescription("Item L description");
        $itemL->setValueStart(15);
        $manager->persist($itemL);

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
