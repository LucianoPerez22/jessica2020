<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ventas
 *
 * @ORM\Table(name="ventas", indexes={@ORM\Index(name="id_cliente", columns={"id_cliente"})})
 * @ORM\Entity
 */
class Ventas
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
     * @var string|null
     *
     * @ORM\Column(name="hora", type="string", length=20, nullable=true)
     */
    private $hora;

    /**
     * @var float|null
     *
     * @ORM\Column(name="total", type="float", precision=10, scale=0, nullable=true)
     */
    private $total;

    /**
     * @var string
     *
     * @ORM\Column(name="forma_pago", type="string", length=20, nullable=false)
     */
    private $formaPago;

    /**
     * @var string
     *
     * @ORM\Column(name="estado", type="string", length=20, nullable=false)
     */
    private $estado;

    /**
     * @var int|null
     *
     * @ORM\Column(name="numero", type="integer", nullable=true)
     */
    private $numero;

    /**
     * @var int|null
     *
     * @ORM\Column(name="cae", type="bigint", nullable=true)
     */
    private $cae;

    /**
     * @var string|null
     *
     * @ORM\Column(name="tipo", type="string", length=2, nullable=true)
     */
    private $tipo;

    /**
     * @var \ListaDeUsuarios
     *
     * @ORM\ManyToOne(targetEntity="ListaDeUsuarios")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_cliente", referencedColumnName="ID")
     * })
     */
    private $idCliente;

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

    public function getHora(): ?string
    {
        return $this->hora;
    }

    public function setHora(?string $hora): self
    {
        $this->hora = $hora;

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

    public function getFormaPago(): ?string
    {
        return $this->formaPago;
    }

    public function setFormaPago(string $formaPago): self
    {
        $this->formaPago = $formaPago;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(?int $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getCae(): ?string
    {
        return $this->cae;
    }

    public function setCae(?string $cae): self
    {
        $this->cae = $cae;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(?string $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getIdCliente(): ?ListaDeUsuarios
    {
        return $this->idCliente;
    }

    public function setIdCliente(?ListaDeUsuarios $idCliente): self
    {
        $this->idCliente = $idCliente;

        return $this;
    }


}
