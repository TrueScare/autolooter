<?php

namespace App\Struct;

use Countable;

/**
 *
 */
class ProbabilityEntryCollection implements \Iterator, Countable
{
    /** @var ProbabilityEntry[] $entries */
    private array $entries;

    /**
     * @param ProbabilityEntry[] $entries
     */
    public function __construct(array $entries = [])
    {
        $this->entries = $entries;
    }

    /**
     * @param ProbabilityEntry $entry
     * @return $this
     */
    public function addEntry(ProbabilityEntry $entry): static
    {
        $this->entries[$entry->getId()] = $entry;
        return $this;
    }

    /**
     * @param ProbabilityEntry $entry
     * @return $this
     */
    public function removeEntry(ProbabilityEntry $entry): static
    {
        if (!empty($this->entries[$entry->getId()])) {
            unset($this->entries[$entry->getId()]);
        }

        return $this;
    }

    /**
     * Returns the entry for the given key/id or null
     * @param int $key
     * @return ProbabilityEntry | null
     */
    public function getEntryByKey(int $key): ProbabilityEntry|null
    {
        if (array_key_exists($key, $this->entries)) {
            return $this->entries[$key];
        }

        return null;
    }

    /**
     * @return ProbabilityEntry[]
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    public function keyExists(int $key): bool
    {
        return key_exists($key, $this->entries);
    }

    public function getKeys(): array
    {
        return array_keys($this->entries);
    }

    public function end(): ProbabilityEntry|false
    {
        return end($this->entries);
    }

    /**
     * Removes all entries.
     * @return void
     */
    public function clear(): void
    {
        $this->entries = [];
    }

    /**
     * Takes in a sql raw result row and adds it as a ProbabilityEntry
     *
     * @param array $entry
     * @return $this
     */
    public function addEntryFromSQLResult(array $entry): static
    {
        $this->addEntry(
            new ProbabilityEntry(
                $entry['id'],
                $entry['parent_id'],
                $entry['name'],
                $entry['individual_probability'],
                $entry['rarity_value'],
            )
        );
        return $this;
    }

    public function buildCollectionFromSQLResult(array $entries): ProbabilityEntryCollection
    {
        foreach ($entries as $entry) {
            $this->addEntryFromSQLResult($entry);
        }

        return $this;
    }

    public function getFilteredResult($filterFunction): ProbabilityEntryCollection
    {
        return new ProbabilityEntryCollection(array_filter($this->entries, $filterFunction));
    }

    public function current(): ProbabilityEntry|false
    {
        return current($this->entries);
    }

    public function next(): void
    {
        next($this->entries);
    }

    public function key(): mixed
    {
        return key($this->entries);
    }

    public function valid(): bool
    {
        return key($this->entries) !== null;
    }

    public function rewind(): void
    {
        reset($this->entries);
    }

    public function shift(): ?ProbabilityEntry
    {
        return array_shift($this->entries);
    }

    public function push(ProbabilityEntry $entry): int
    {
        return array_push($this->entries, $entry);
    }

    public function count(): int
    {
        return count($this->entries);
    }

    public function getTotalProbability(): float
    {
        $probability = 0;

        foreach ($this->entries as $entry) {
            $probability += $entry->getIndividualProbability();
        }

        return $probability;
    }

    public function getSumValues(): int
    {
        $value = 0;
        foreach ($this->entries as $entry) {
            $value += $entry->getRarityValue();
        }

        return $value;
    }
}