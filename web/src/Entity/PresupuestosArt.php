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
     * @ORM\Column(name="cant", type="integer", nullable=false)
     */
    private $cantidad;
    
  
    /**     
     * @ORM\ManyToOne(targetEntity="Presupuestos", inversedBy="articulo")
     * @ORM\JoinColumn(name="id_presupuesto", referencedColumnName="id")     
     */
    private $presupuesto;

    /**
     * @ORM\ManyToOne(targetEntity="Articulos", inversedBy="articulo")
     * @ORM\JoinColumn(name="id_art", referencedColumnName="id")   
     */
    private $idArt;

    /**
     * @var float
     *
     * @ORM\Column(name="precio", type="float", precision=10, scale=0, nullable=false)
     */
    private $precio;

     /**
     * @var float
     *
     * @ORM\Column(name="total", type="float", precision=10, scale=0, nullable=false)
     */
    private $total;

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

    public function getPresupuesto()
    {
        return $this->presupuesto;
    }

    public function setPresupuesto($presupuesto): self
    {
        $this->presupuesto = $presupuesto;

        return $this;
    }

    public function getIdArt(): ?Articulos
    {
        return $this->idArt;
    }

    public function setIdArt(?Articulos $idArt): self
    {
        $this->idArt = $idArt;

        return $this;
    }

    public function getPrecio(): ?float
    {
        return $this->precio;
    }

    public function setPrecio(float $precio): self
    {
        $this->precio = $precio;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;

        return $this;
    }  
}
