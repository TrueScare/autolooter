<?php

namespace App\Struct;

use App\Entity\Table;

class RandomItemConfig
{
    private int $amount = 1;
    /**
     * @var Table[]
     */
    private array $tables;

    private bool $uniqueTables;

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        if(!empty($amount) || $amount >= 1){
            $this->amount = $amount;
        }else {
            $this->amount = 1;
        }

    }

    /**
     * @return Table[]
     */
    public function getTables(): array
    {
        return $this->tables;
    }

    /**
     * @param Table[] $tables
     * @return void
     */
    public function setTables(array $tables): void
    {
        $this->tables = $tables;
    }

    public function isUniqueTables(): bool
    {
        return $this->uniqueTables;
    }

    public function setUniqueTables(bool $uniqueTables): void
    {
        $this->uniqueTables = $uniqueTables;
    }
}