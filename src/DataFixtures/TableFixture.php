<?php

namespace App\DataFixtures;

use App\Entity\Table;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
;

class TableFixture extends Fixture implements DependentFixtureInterface
{
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
        for($i = 0; $i < 5; $i++) {
            for ($j = 0; $j < 5; $j++) {
                $table = new Table();
                $table->setName('table' . $i . '.' . $j);
                $table->setDescription('table description' . $i . '.' . $j);
                $table->setRarity($rarityCollection[array_rand($rarityCollection, 1)]);
                $table->setOwner($owner);

                // set random parent from previous layer
                if($i-1 >= 0){
                    $table->setParent($tables[$i-1][rand(0, count($tables)-1)]);
                }

                $tables[$i][] = $table;

                $manager->persist($table);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
            RarityFixture::class
        ];
    }
}
