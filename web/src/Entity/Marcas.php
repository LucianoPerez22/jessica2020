<?php

namespace App\Entity;

use App\Repository\MarcasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=MarcasRepository::class)
 * @UniqueEntity("descripcion")
 */
class Marcas
{
    /**
     * @var int
     *
     * @ORM\OneToMany(targetEntity="Articulos", mappedBy="idMarca")
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="string", length=200, nullable=false)
     * @Assert\NotBlank()
     */
    private $descripcion;
  
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * @return Collection|Articulos[]
     */
    public function getArticulos(): Collection
    {
        return $this->articulos;
    }

    public function addArticulo(Articulos $articulo): self
    {
        if (!$this->articulos->contains($articulo)) {
            $this->articulos[] = $articulo;
            $articulo->setIdMarca($this);
        }

        return $this;
    }

    public function removeArticulo(Articulos $articulo): self
    {
        if ($this->articulos->removeElement($articulo)) {
            // set the owning side to null (unless already changed)
            if ($articulo->getIdMarca() === $this) {
                $articulo->setIdMarca(null);
            }
        }

        return $this;
    }


}
