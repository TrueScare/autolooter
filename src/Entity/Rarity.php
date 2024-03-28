<?php

namespace App\Entity;

use App\Repository\RarityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RarityRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Rarity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $value = null;

    #[ORM\OneToMany(mappedBy: 'rarity', targetEntity: Item::class)]
    private Collection $items;

    #[ORM\OneToMany(mappedBy: 'rarity', targetEntity: Table::class)]
    private Collection $tables;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $color = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'rarities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->tables = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): static
    {
        $this->value = $value;

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
            $item->setRarity($this);
        }

        return $this;
    }

    public function removeItem(Item $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getRarity() === $this) {
                $item->setRarity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Table>
     */
    public function getTables(): Collection
    {
        return $this->tables;
    }

    public function addTable(Table $table): static
    {
        if (!$this->tables->contains($table)) {
            $this->tables->add($table);
            $table->setRarity($this);
        }

        return $this;
    }

    public function removeTable(Table $table): static
    {
        if ($this->tables->removeElement($table)) {
            // set the owning side to null (unless already changed)
            if ($table->getRarity() === $this) {
                $table->setRarity(null);
            }
        }

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

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

    /**
     * @param PreRemoveEventArgs $args
     * @return void
     */
    #[ORM\PreRemove]
    public function preRemove(PreRemoveEventArgs $args): void
    {
        if (empty($this->getTables())
            && empty($this->getItems())
        ) {
            return;
        }

        $tables = $this->getTables();
        $items = $this->getItems();

        if ($tables->count() > 0 || $items->count() > 0) {
            $rarity = new Rarity();
            $rarity->setName('Lost and Found');
            $rarity->setDescription('Lost and Found');
            $rarity->setValue(0);
            $rarity->setColor('#fff');
            $rarity->setOwner($this->getOwner());


            if ($items->count() > 0) {
                foreach ($items as $item) {
                    $item->setRarity($rarity);
                }
            }

            if ($tables->count() > 0) {
                foreach ($tables as $table) {
                    $table->setRarity($rarity);
                }
            }

            $args->getObjectManager()->persist($rarity);
        }
    }

    public function getTableCollectionRecursive(array $tables = [])
    {
        if ($this->getTables()->count() <= 0) {
            return $tables;
        }

        foreach ($this->getTables() as $child) {
            $tables[$child->getId()] = $child;
            $tables = $child->getChildrenCollectionRecursive($tables);
        }

        return $tables;
    }
}
