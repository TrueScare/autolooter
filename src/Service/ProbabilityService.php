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

    public function getTableProbabilities(User $owner)
    {
        $data = $this->tableRepository->getAllTableIndividualRarities($owner, $this->entityManager);
        $data = $this->mapResultArray($data);

        $queue = array_filter($data, function ($item) {
            return $item['parent_id'] == null;
        });

        return $this->prepareProbabilities($data, $queue);
    }

    public function getItemProbabilities(User $owner)
    {
        $data = $this->itemRepository->getAllItemIndividualRarities($owner, $this->entityManager);
        $data = $this->mapResultArray($data);
        $table_data = $this->getTableProbabilities($owner);

        foreach($data as &$item){
            $item['individual_rarity'] = $item['individual_rarity'] * $table_data[$item['parent_id']]['individual_rarity'];
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
        while (!empty($current = array_shift($queue))) {
            foreach ($data as &$child) {
                if ($child['parent_id'] == $current['id']) {
                    $child['individual_rarity'] = $child['individual_rarity'] * $current['individual_rarity'];
                    array_push($queue, $child);
                }
            }
        }
        return $data;
    }
}