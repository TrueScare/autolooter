<?php

namespace App\Struct;

class ProbabilityEntry
{
    private int $id;
    private ?int $parentId;
    private string $name;
    private float $individualProbability;
    private float $rarityValue;

    /**
     * @param int $id
     * @param int|null $parentId
     * @param string $name
     * @param float $individualProbability
     * @param float $rarityValue
     */
    public function __construct(int $id, ?int $parentId, string $name, float $individualProbability, float $rarityValue)
    {
        $this->id = $id;
        $this->parentId = $parentId;
        $this->name = $name;
        $this->individualProbability = $individualProbability;
        $this->rarityValue = $rarityValue;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getIndividualProbability(): float
    {
        return $this->individualProbability;
    }

    /**
     * @param float $individualProbability
     * @return void
     */
    public function setIndividualProbability(float $individualProbability): void
    {
        $this->individualProbability = $individualProbability;
    }

    /**
     * @return float
     */
    public function getRarityValue(): float
    {
        return $this->rarityValue;
    }
}