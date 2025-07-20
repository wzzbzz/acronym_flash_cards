<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Acronym>
     */
    #[ORM\OneToMany(targetEntity: Acronym::class, mappedBy: 'category')]
    private Collection $acronyms;

    public function __construct()
    {
        $this->acronyms = new ArrayCollection();
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

    /**
     * @return Collection<int, Acronym>
     */
    public function getAcronyms(): Collection
    {
        return $this->acronyms;
    }

    public function addAcronym(Acronym $acronym): static
    {
        if (!$this->acronyms->contains($acronym)) {
            $this->acronyms->add($acronym);
            $acronym->setCategory($this);
        }

        return $this;
    }

    public function removeAcronym(Acronym $acronym): static
    {
        if ($this->acronyms->removeElement($acronym)) {
            // set the owning side to null (unless already changed)
            if ($acronym->getCategory() === $this) {
                $acronym->setCategory(null);
            }
        }

        return $this;
    }
}
