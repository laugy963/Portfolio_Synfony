<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $smallDescription = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bannerImage = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $images = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $link = null;

    #[ORM\Column(length: 2000, nullable: true)]
    private ?string $technologies = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $madeBy = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getSmallDescription(): ?string
    {
        return $this->smallDescription;
    }

    public function setSmallDescription(?string $smallDescription): static
    {
        $this->smallDescription = $smallDescription;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getBannerImage(): ?string
    {
        return $this->bannerImage;
    }

    public function setBannerImage(?string $bannerImage): static
    {
        $this->bannerImage = $bannerImage;
        return $this;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function setImages(array $images): static
    {
        $this->images = $images;
        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;
        return $this;
    }

    public function getTechnologies(): ?string
    {
        return $this->technologies;
    }

    public function setTechnologies(?string $technologies): static
    {
        $this->technologies = $technologies;
        return $this;
    }

    public function getTechnologiesArray(): array
    {
        return $this->technologies ? explode(',', $this->technologies) : [];
    }

    public function setTechnologiesFromArray(array $technologies): static
    {
        $this->technologies = implode(',', $technologies);
        return $this;
    }

    public function getMadeBy(): ?string
    {
        return $this->madeBy;
    }

    public function setMadeBy(?string $madeBy): static
    {
        $this->madeBy = $madeBy;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
