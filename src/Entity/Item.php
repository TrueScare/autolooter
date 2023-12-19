<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Entity\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Vich\UploaderBundle\Mapping\Annotation\Uploadable;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
#[Uploadable]
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

    #[Vich\UploadableField(mapping: 'items', fileNameProperty: 'imageName', size: 'imageSize')]
    private ?File $imageFile = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    private ?int $imageSize = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

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
        return $this->getParent()->getPathToRoot();
    }

    public function getCollectionToRoot(){
        return $this->getParent()->getCollectionRoot();
    }

    public function getPeers()
    {
        $peers = $this->getParent()->getItems();

        $peers = $peers->filter(function ($element) {
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

        $probability = $this->getRarity()->getValue() / ($volume + $this->getRarity()->getValue());
        $parentProbability = $this->getParent()->getProbability();

        //return ($probability * $parentProbability);
        return $probability * $parentProbability;
    }
    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }
}
