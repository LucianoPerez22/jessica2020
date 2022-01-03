<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @var int
     *
     * @ORM\Column(name="id_ventas", type="integer", nullable=false)
     */
    private $idVentas;

    /**
     * @var int
     *
     * @ORM\Column(name="id_art", type="integer", nullable=false)
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdVentas(): ?int
    {
        return $this->idVentas;
    }

    public function setIdVentas(int $idVentas): self
    {
        $this->idVentas = $idVentas;

        return $this;
    }

    public function getIdArt(): ?int
    {
        return $this->idArt;
    }

    public function setIdArt(int $idArt): self
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


}
