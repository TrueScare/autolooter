<?php

namespace App\Entity;

use App\Repository\TableRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TableRepository::class)]
#[ORM\Table(name: '`table`')]
class Table
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tables')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'tables')]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $tables;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Item::class)]
    private Collection $items;

    #[ORM\ManyToOne(inversedBy: 'tables')]
    private ?Rarity $rarity = null;

    public function __construct()
    {
        $this->tables = new ArrayCollection();
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getTables(): Collection
    {
        return $this->tables;
    }

    public function addTable(self $table): static
    {
        if (!$this->tables->contains($table)) {
            $this->tables->add($table);
            $table->setParent($this);
        }

        return $this;
    }

    public function removeTable(self $table): static
    {
        if ($this->tables->removeElement($table)) {
            // set the owning side to null (unless already changed)
            if ($table->getParent() === $this) {
                $table->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Item>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Item $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setParent($this);
        }

        return $this;
    }

    public function removeItem(Item $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getParent() === $this) {
                $item->setParent(null);
            }
        }

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

    /**
     * List of Tables on the Path to root in order of occurrence from bottom to top
     * @return array
     */
    public function getPathToRoot($path = [])
    {
        // add yourself to the queue
        $path[] = $this;

        if (empty($this->getParent())) {
            // you are the root? splendid!
            return $path;
        } else {
            // search on for the root
            return $this->getParent()->getPathToRoot($path);
        }
    }

    /**
     * Used to generate the pool of Tables that are in the path to the root
     * @param $path
     * @return array|mixed
     */
    public function getCollectionRoot($path = [])
    {
        // add yourself to the queue
        $path[$this->getId()] = $this;

        if (empty($this->getParent())) {
            // you are the root? splendid!
            return $path;
        } else {
            // search on for the root
            return $this->getParent()->getPathToRoot($path);
        }
    }

    public function getPeers()
    {
        if (empty($this->getParent())) {
            $parentTables = $this->getOwner()->getRootTables();
        } else {
            $parentTables = $this->getParent()->getTables();
        }

        $parentTables = $parentTables->filter(function ($element) {
            /** @var Table $element */
            return $element->getId() != $this->getId();
        });

        return $parentTables;
    }

    public function getProbability($probability = 1)
    {
        $peers = $this->getPeers();
        $volume = 0;
        foreach ($peers as $peer) {
            // rule out all tables that do not result in an item
            /** @var Table $peer */
            $volume += $peer->hasPathToItems() ? $peer->getRarity()->getValue() : 0;
        }

        if ($this->hasPathToItems()) {
            $probability *= $this->getRarity()->getValue() / ($volume + $this->getRarity()->getValue());
        } else {
            $probability = 0;
        }

        if (empty($this->getParent())) {
            return $probability;
        }

        return $this->getParent()->getProbability($probability);
    }

    public function getChildrenCollectionRecursive($tables = [])
    {
        if (empty($this->getTables())) {
            return $tables;
        }

        foreach ($this->getTables() as $child) {
            $tables[$child->getId()] = $child;
            $tables = $child->getChildrenCollectionRecursive($tables);
        }

        return $tables;
    }

    public function hasPathToItems(): bool
    {
        if ($this->getItems()->count() > 0) {
            return true;
        }

        $children = $this->getChildrenCollectionRecursive();
        /** @var Table $child */
        foreach ($children as $child) {
            if ($child->getItems()->count() > 0) {
                return true;
            }
        }
        return false;
    }
}
