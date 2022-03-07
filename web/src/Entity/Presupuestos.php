<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PresupuestoRepository;


/**
 * @ORM\Entity(repositoryClass=PresupuestoRepository::class)
 */
class Presupuestos
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="fecha", type="date", nullable=true)
     */
    private $fecha;

     /**
     * @var string
     *
     * @ORM\Column(name="cliente", type="string", length=255, nullable=false)
     */
    private $cliente;
  
    /**
     * @var float|null
     *
     * @ORM\Column(name="total", type="float", precision=10, scale=0, nullable=true)
     */
    private $total;

   
    /**   
     * @ORM\Column(name="user", type="string", length=255, nullable=true)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=PresupuestosArt::class, mappedBy="presupuesto")
     */
    private $articulo;

    public function __construct()
    {
        $this->articulo = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(?\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getCliente(): ?string
    {
        return $this->cliente;
    }

    public function setCliente(string $cliente): self
    {
        $this->cliente = $cliente;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(?string $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|PresupuestosArt[]
     */
    public function getArticulo(): Collection
    {
        return $this->articulo;
    }

    public function addArticulo(PresupuestoArt $articulo): self
    {
        if (!$this->articulo->contains($articulo)) {
            $this->articulo[] = $articulo;
            $articulo->setPresupuesto($this);
        }

        return $this;
    }

    public function removeArticulo(PresupuestoArt $articulo): self
    {
        if ($this->articulo->removeElement($articulo)) {
            // set the owning side to null (unless already changed)
            if ($articulo->getPresupuesto() === $this) {
                $articulo->setPresupuesto(null);
            }
        }

        return $this;
    }
}
