<?php

namespace App\Entity;

use App\Repository\ArticulosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ArticulosRepository::class)
 */
class Articulos
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
     * @var int|null
     *
     * @ORM\ManyToOne(targetEntity="Marcas", inversedBy="id")
     * @ORM\JoinColumn(name="id_marca", referencedColumnName="id")
     */
    private $idMarca;

    /**
     * @var string|null
     *
     * @ORM\Column(name="codigo", type="string", length=40, nullable=true)
     */
    private $codigo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="descripcion", type="string", length=300, nullable=true)
     */
    private $descripcion;

    /**
     * @var float|null
     *
     * @ORM\Column(name="precio", type="float", precision=10, scale=0, nullable=true)
     */
    private $precio;

    /**
     * @var float|null
     *
     * @ORM\Column(name="ganancia", type="float", precision=10, scale=0, nullable=true)
     */
    private $ganancia;    

    public function __construct()
    {    
        $this->idMarca = new ArrayCollection();        
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdMarca(): ?Marcas
    {
        return $this->idMarca;
    }

    public function setIdMarca(?Marcas $idMarca): self
    {
        $this->idMarca = $idMarca;

        return $this;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(?string $codigo): self
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getPrecio(): ?float
    {
        return $this->precio;
    }

    public function setPrecio(?float $precio): self
    {
        $this->precio = $precio;

        return $this;
    }

    public function getGanancia(): ?float
    {
        return $this->ganancia;
    }

    public function setGanancia(?float $ganancia): self
    {
        $this->ganancia = $ganancia;

        return $this;
    }


}
