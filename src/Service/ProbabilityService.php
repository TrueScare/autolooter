<?php

namespace App\Service;

use App\Entity\Item;
use App\Entity\User;
use App\Repository\ItemRepository;
use App\Repository\TableRepository;
use App\Struct\ProbabilityEntry;
use App\Struct\ProbabilityEntryCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ProbabilityService
{
    private EntityManagerInterface $entityManager;
    private TableRepository $tableRepository;
    private ItemRepository $itemRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        TableRepository        $tableRepository,
        ItemRepository         $itemRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->tableRepository = $tableRepository;
        $this->itemRepository = $itemRepository;
    }

    public function getTableProbabilities(User $owner, array $subsetIds = []): ProbabilityEntryCollection
    {
        $collection = $this->tableRepository->getAllTableIndividualRarities($owner, $this->entityManager);

        if (empty($subsetIds)) {
            $queue = $collection->getFilteredResult(function (ProbabilityEntry $item) {
                return $item->getParentId() == null;
            });
        } else {
            $queue = $collection->getFilteredResult(function (ProbabilityEntry $item) use ($subsetIds) {
                return in_array($item->getId(), $subsetIds);
            });

            $sum = 0;
            foreach ($queue as $item) {
                $sum += $item->getRarityValue();
            }

            foreach ($collection as $item) {
                if (in_array($item->getId(), $subsetIds)) {
                    $item->setIndividualProbability($item->getRarityValue() / $sum);
                }
            }
        }

        return $this->prepareProbabilities($collection, $queue);
    }

    public function getItemProbabilities(User $owner, ProbabilityEntryCollection $probabilityMapping): ProbabilityEntryCollection
    {
        $collection = $this->itemRepository->getAllItemIndividualRarities($owner, $this->entityManager);

        foreach ($collection as $item) {
            if ($parent = $probabilityMapping->getEntryByKey($item->getParentId())) {
                $item->setIndividualProbability($item->getIndividualProbability() * $parent->getIndividualProbability());
            } else {
                $item->setIndividualProbability(0);
            }
        }

        return $collection;
    }

    private function prepareProbabilities(ProbabilityEntryCollection $collection, ProbabilityEntryCollection $queue): ProbabilityEntryCollection
    {
        $visited = [];

        while (!empty($current = $queue->shift())) {
            $visited[] = $current->getId();
            foreach ($collection as $child) {
                if ($child->getParentId() == $current->getId() && !$queue->keyExists($child->getId())) {
                    $child->setIndividualProbability($child->getIndividualProbability() * $current->getIndividualProbability());
                    $queue->push($child);
                }
            }
        }

        return $collection->getFilteredResult(function (ProbabilityEntry $item) use ($visited) {
            return in_array($item->getId(), $visited);
        });
    }
}