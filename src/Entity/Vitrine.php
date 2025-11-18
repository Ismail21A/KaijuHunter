<?php

namespace App\Entity;

use App\Repository\VitrineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VitrineRepository::class)]
class Vitrine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    /**
     * @var Collection<int, Figure>
     */
    #[ORM\OneToMany(targetEntity: Figure::class, mappedBy: 'vitrine')]
    private Collection $figures;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Member $owner = null;

    public function __construct()
    {
        $this->figures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, Figure>
     */
    public function getFigures(): Collection
    {
        return $this->figures;
    }

    public function addFigure(Figure $figure): static
    {
        if (!$this->figures->contains($figure)) {
            $this->figures->add($figure);
            $figure->setVitrine($this);
        }

        return $this;
    }

    public function removeFigure(Figure $figure): static
    {
        if ($this->figures->removeElement($figure)) {
            // set the owning side to null (unless already changed)
            if ($figure->getVitrine() === $this) {
                $figure->setVitrine(null);
            }
        }

        return $this;
    }

    public function getOwner(): ?Member
    {
        return $this->owner;
    }

    public function setOwner(?Member $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
