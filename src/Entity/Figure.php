<?php

namespace App\Entity;

use App\Repository\FigureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FigureRepository::class)]
class Figure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(length: 255)]
    private ?string $name = null;
    
    #[ORM\ManyToOne(inversedBy: 'figures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vitrine $vitrine = null;
    
    /**
     * @var Collection<int, Arena>
     */
    #[ORM\ManyToMany(targetEntity: Arena::class, mappedBy: 'figures')]
    private Collection $arenas;
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;   
    
    public function __construct()
    {
        $this->arenas = new ArrayCollection();
    }
    
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
    
    public function getVitrine(): ?Vitrine
    {
        return $this->vitrine;
    }
    
    public function setVitrine(?Vitrine $vitrine): static
    {
        $this->vitrine = $vitrine;
        
        return $this;
    }
    
    /**
     * @return Collection<int, Arena>
     */
    public function getArenas(): Collection
    {
        return $this->arenas;
    }
    
    public function addArena(Arena $arena): static
    {
        if (!$this->arenas->contains($arena)) {
            $this->arenas->add($arena);
            $arena->addFigure($this);
        }
        
        return $this;
    }
    
    public function removeArena(Arena $arena): static
    {
        if ($this->arenas->removeElement($arena)) {
            $arena->removeFigure($this);
        }
        
        return $this;
    }
    
    
    public function getImageName(): ?string
    {
        return $this->imageName;
    }
    
    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;
        
        return $this;
    }
}
