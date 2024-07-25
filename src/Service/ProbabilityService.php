<?php

namespace App\Service;

use App\Entity\Item;
use App\Entity\User;
use App\Exceptions\NoItemFoundException;
use App\Repository\ItemRepository;
use App\Repository\TableRepository;
use App\Struct\ProbabilityEntry;
use App\Struct\ProbabilityEntryCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProbabilityService
{
    private EntityManagerInterface $entityManager;
    private TableRepository $tableRepository;
    private ItemRepository $itemRepository;
    private LoggerInterface $logger;
    private TranslatorInterface $translator;

    public function __construct(
        EntityManagerInterface $entityManager,
        TableRepository        $tableRepository,
        ItemRepository         $itemRepository,
        TranslatorInterface    $translator,
        LoggerInterface        $logger
    )
    {
        $this->entityManager = $entityManager;
        $this->tableRepository = $tableRepository;
        $this->itemRepository = $itemRepository;
        $this->logger = $logger;
        $this->translator = $translator;
    }

    /**
     * @param User $owner
     * @param int[] $subsetIds
     * @return ProbabilityEntryCollection
     */
    public function getTableProbabilities(User $owner, array $subsetIds = []): ProbabilityEntryCollection
    {
        $collection = $this->tableRepository->getAllTableIndividualRarities($owner, $this->entityManager);

        if (empty($subsetIds)) {
            // get the "root" tables
            $queue = $collection->getFilteredResult(function (ProbabilityEntry $item) {
                return $item->getParentId() == null;
            });
        } else {
            // build queue from selected ids
            $queue = $collection->getFilteredResult(function (ProbabilityEntry $item) use ($subsetIds) {
                return in_array($item->getId(), $subsetIds);
            });

            $sum = $queue->getTotalProbability();

            foreach ($collection as $item) {
                if (in_array($item->getId(), $subsetIds)) {
                    $item->setIndividualProbability($item->getRarityValue() / $sum);
                }
            }
        }

        return $this->prepareProbabilities($collection, $queue);
    }

    /**
     * @param User $owner
     * @param ProbabilityEntryCollection|null $probabilityMapping
     * @param array $rarities
     * @return ProbabilityEntryCollection
     */
    public function getItemProbabilities(User $owner, ProbabilityEntryCollection $probabilityMapping = null, array $rarities = []): ProbabilityEntryCollection
    {
        if ($probabilityMapping == null) {
            $probabilityMapping = $this->getTableProbabilities($owner);
        }

        $collection = $this->itemRepository->getAllItemIndividualRarities($owner, $this->entityManager, $rarities);

        foreach ($collection as $item) {
            if ($parent = $probabilityMapping->getEntryByKey($item->getParentId())) {
                $item->setIndividualProbability($item->getIndividualProbability() * $parent->getIndividualProbability());
            } else {
                $item->setIndividualProbability(0);
            }
        }

        return $collection;
    }

    /**
     * Produces an array of ids of items from the ProbabilityEntryCollection
     *
     * @param ProbabilityEntryCollection $probabilityMapping
     * @param int $amount
     * @param bool $uniqueItems
     * @return array
     * @throws NoItemFoundException
     */
    public function pickMultipleFromItems(ProbabilityEntryCollection $probabilityMapping, int $amount = 1, bool $uniqueItems = false): array
    {
        $keys = [];
        if (count($probabilityMapping) <= 0) {
            $this->logger->info($this->translator->trans('item.random.notEnoughItems'));
        } else if (count($probabilityMapping) <= $amount && $uniqueItems) {
            if (count($probabilityMapping) < $amount) {
                $this->logger->info($this->translator->trans('item.random.notEnoughItems'));
            }
            //only way to return unique items... we may not get the wanted amount though...
            $keys = $probabilityMapping->getKeys();
        } else if (count($probabilityMapping) > $amount && $uniqueItems) {
            $keys = $this->getPickFromItemsUnique($probabilityMapping, $amount);

        } else {
            while (count($keys) < $amount) {
                $keys[] = $this->getPickFromItems($probabilityMapping);
            }
        }

        return $keys;
    }

    /**
     * @throws NoItemFoundException
     */
    private function getPickFromItems(ProbabilityEntryCollection $probabilityMapping): int
    {
        // calculate random value in between 0 and total individual probability value
        $luckyPick = lcg_value() * (abs($probabilityMapping->getTotalProbability()));

        foreach ($probabilityMapping as $item) {
            $luckyPick -= $item->getIndividualProbability();
            if ($luckyPick <= 0) {
                return $item->getId();
            }
        }

        $this->logger->error("No item found... that's suspicious.");
        throw new NoItemFoundException($probabilityMapping);
    }

    /**
     * @param ProbabilityEntryCollection $probabilityMapping
     * @param $amount
     * @return array
     * @throws NoItemFoundException
     */
    public function getPickFromItemsUnique(ProbabilityEntryCollection $probabilityMapping, $amount): array
    {
        $baseProbability = 1;
        $picks = [];

        for ($i = 0; $i < $amount; $i++) {

            $luckyPick = (mt_rand() / mt_getrandmax()) * $baseProbability;

            foreach ($probabilityMapping as $key => $item) {
                $luckyPick = $luckyPick - $item->getIndividualProbability();

                if ($luckyPick <= 0) {
                    $picks[$item->getId()] = $item->getId();
                    $baseProbability = $baseProbability - $item->getIndividualProbability();
                    $probabilityMapping->removeEntry($item);
                    break;
                }
            }
        }

        if (count($picks) <= 0) {
            $this->logger->error("No item found... that's suspicious.");
            throw new NoItemFoundException($probabilityMapping);
        }

        return $picks;
    }

    /**
     * @param ProbabilityEntryCollection $collection
     * @param ProbabilityEntryCollection $queue
     * @return ProbabilityEntryCollection
     */
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