<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\VentasRecurrentesRepository;


/**
 * @ORM\Entity(repositoryClass=VentasRecurrentesRepository::class)
 */
class VentasRecurrentes
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
     * @var \ListaDeUsuarios
     *
     * @ORM\ManyToOne(targetEntity="ListaDeUsuarios")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_cliente", referencedColumnName="ID")
     * })
     */
    private $idCliente;

    /**
     * @var float|null
     *
     * @ORM\Column(name="total", type="float", precision=10, scale=0, nullable=true)
     */
    private $total;

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
     * @var \DateTime|null
     *
     * @ORM\Column(name="caeVenc", type="date", nullable=true)
     */
    private $caeVenc;

    /**
     * @ORM\Column(name="user", type="string", length=255, nullable=true)
     */
    private $user;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="createdAt", type="date", nullable=true)
     */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new Datetime("now");
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

    public function getIdCliente(): ?ListaDeUsuarios
    {
        return $this->idCliente;
    }

    public function setIdCliente(?ListaDeUsuarios $idCliente): self
    {
        $this->idCliente = $idCliente;

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

    public function getCaeVenc(): ?\DateTimeInterface
    {
        return $this->caeVenc;
    }

    public function setCaeVenc(?\DateTimeInterface $caeVenc): self
    {
        $this->caeVenc = $caeVenc;

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
     * @return DateTime
     */
    public function getTimestamp(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     * @return self
     */
    public function setTimestamp(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
