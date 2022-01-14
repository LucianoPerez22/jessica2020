<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
/**
 * VentasArt
 *
 * @ORM\Table(name="ventas_art")
 * @ORM\Entity
 */
class VentasArt
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
     * @ORM\ManyToOne(targetEntity="Ventas", inversedBy="ventas")
     * @ORM\JoinColumn(name="id_ventas", referencedColumnName="id")     
     */
    private $idVentas;

    /**
     * @ORM\ManyToOne(targetEntity="Articulos", inversedBy="articulo")
     * @ORM\JoinColumn(name="id_art", referencedColumnName="id")   
     */
    private $idArt;
    
    /**
     * @var float
     *
     * @ORM\Column(name="cant", type="float", precision=10, scale=0, nullable=false)
     */
    private $cant;

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

    public function __construct()
    {
        //$this->articulo = new ArrayCollection();    
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdVentas(): ?Ventas
    {
        return $this->idVentas;
    }

    public function setIdVentas(?Ventas $idVentas): self
    {
        $this->idVentas = $idVentas;

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

    public function getCant(): ?float
    {
        return $this->cant;
    }

    public function setCant(float $cant): self
    {
        $this->cant = $cant;

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
