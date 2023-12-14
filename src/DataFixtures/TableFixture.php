<?php

namespace App\DataFixtures;

use App\Entity\Table;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
;

class TableFixture extends Fixture implements DependentFixtureInterface
{
    public const BASE_TABLE = 'base_table';
    public function load(ObjectManager $manager): void
    {
        $rarityCollection = [
            $this->getReference(RarityFixture::RARITY_COMMON),
            $this->getReference(RarityFixture::RARITY_RARE),
            $this->getReference(RarityFixture::RARITY_SUPERRARE),
            $this->getReference(RarityFixture::RARITY_ULTRARARE),
        ];
        $owner = $this->getReference(UserFixture::BASE_USER);

        $tables = [];
        for($i = 0; $i < 15; $i++){
            $table = new Table();
            $table->setName('table' . $i);
            $table->setDescription('table description' .$i);
            $table->setRarity($rarityCollection[array_rand($rarityCollection, 1)]);
            $table->setOwner($owner);
            $tables[] = $table;

            $manager->persist($table);
        }

        $manager->flush();

        $this->addReference(self::BASE_TABLE, $tables[0]);
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
            RarityFixture::class
        ];
    }
}
