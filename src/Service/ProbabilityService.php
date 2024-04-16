<?php

namespace App\Service;

use App\Entity\Item;
use App\Entity\User;
use App\Repository\ItemRepository;
use App\Repository\TableRepository;
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

    public function getTableProbabilities(User $owner, array $subsetIds = [])
    {
        $data = $this->tableRepository->getAllTableIndividualRarities($owner, $this->entityManager);
        $data = $this->mapResultArray($data);

        if (empty($subsetIds)) {
            $queue = array_filter($data, function ($item) {
                return $item['parent_id'] == null;
            });
        } else {
            $queue = array_filter($data, function ($item) use ($subsetIds) {
                return in_array($item['id'], $subsetIds);
            });

            $sum = 0;
            foreach ($queue as $item) {
                $sum += $item['value'];
            }

            foreach ($data as &$item) {
                if (in_array($item['id'], $subsetIds)) {
                    $item['individual_rarity'] = $item['value'] / $sum;
                }
            }
        }

        return $this->prepareProbabilities($data, $queue);
    }

    public function getItemProbabilities(User $owner, array $probabilityMapping)
    {
        $data = $this->itemRepository->getAllItemIndividualRarities($owner, $this->entityManager);
        $data = $this->mapResultArray($data);

        foreach ($data as &$item) {
            if (!empty($probabilityMapping[$item['parent_id']])) {
                $item['individual_rarity'] = $item['individual_rarity'] * $probabilityMapping[$item['parent_id']]['individual_rarity'];
            } else {
                $item['individual_rarity'] = 0;
            }
        }

        return $data;
    }

    private function mapResultArray(array $data)
    {
        $result = [];
        array_walk($data, function ($item) use (&$result) {
            $result[$item['id']] = $item;
        });
        return $result;
    }

    private function prepareProbabilities(array $data, array $queue): array
    {
        $visited = [];
        while (!empty($current = array_shift($queue))) {
            $visited[] = $current['id'];

            foreach ($data as &$child) {
                if ($child['parent_id'] == $current['id']) {
                    $child['individual_rarity'] = $child['individual_rarity'] * $current['individual_rarity'];
                    array_push($queue, $child);
                }
            }
        }

        return array_filter($data, function ($item) use ($visited) {
            return in_array($item['id'], $visited);
        });
    }
}