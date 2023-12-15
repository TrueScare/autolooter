<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Table $parent = null;

    #[ORM\Column]
    private ?float $value_start = null;

    #[ORM\Column]
    private ?float $value_end = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    private ?Rarity $rarity = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getParent(): ?Table
    {
        return $this->parent;
    }

    public function setParent(?Table $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getValueStart(): ?float
    {
        return $this->value_start;
    }

    public function setValueStart(float $value_start): static
    {
        $this->value_start = $value_start;

        return $this;
    }

    public function getValueEnd(): ?float
    {
        return $this->value_end;
    }

    public function setValueEnd(float $value_end): static
    {
        $this->value_end = $value_end;

        return $this;
    }

    public function getRarity(): ?Rarity
    {
        return $this->rarity;
    }

    public function setRarity(?Rarity $rarity): static
    {
        $this->rarity = $rarity;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getPathToRoot(){
        return $this->getPathToRoot();
    }

    public function getCollectionToRoot(){
        return $this->getParent()->getCollectionRoot();
    }

    public function getPeers()
    {
        $peers = $this->getOwner()->getTables();

        $peers->filter(function ($element) {
            /** @var Item $element */
            return $element->getId() != $this->getId();
        });

        return $peers;
    }

    public function getProbability()
    {
        $peers = $this->getPeers();
        $volume = 0;
        foreach($peers as $peer){
            /** @var Table $peer */
            $volume += $peer->getRarity()->getValue();
        }

        $parentProbability = $this->getParent()->getProbability();
        $probability = $this->getRarity()->getValue() / ($volume + $this->getRarity()->getValue());
        return $parentProbability * $probability;
    }
}
