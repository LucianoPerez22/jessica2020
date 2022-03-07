<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PresupuestoArtRepository;


/**
 * @ORM\Entity(repositoryClass=PresupuestoArtRepository::class)
 */
class PresupuestosArt
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
     * @var int
     *
     * @ORM\Column(name="fecha", type="int", nullable=true)
     */
    private $cantidad;

     /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="string", length=255, nullable=false)
     */
    private $descripcion;
  
    /**
     * @var int
     *@ORM\ManyToOne(targetEntity="Presupuestos", inversedBy="articulo")
     * @ORM\Column(name="id_presupuesto", type="int", nullable=true)
     */
    private $presupuesto;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCantidad()
    {
        return $this->cantidad;
    }

    public function setCantidad($cantidad): self
    {
        $this->cantidad = $cantidad;

        return $this;
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

    public function getPresupuesto()
    {
        return $this->presupuesto;
    }

    public function setPresupuesto($presupuesto): self
    {
        $this->presupuesto = $presupuesto;

        return $this;
    }
}
