<?php

namespace App\Entity;

use App\Repository\ClientesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ClientesRepository::class)
 */
class ListaDeUsuarios
{
    /**
     * @var int
     *
     * @ORM\Column(name="ID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     * @Assert\NotBlank()
     * @ORM\Column(name="Nombre", type="string", length=46, nullable=false)
     */
    private $nombre;

    /**
     * @var string|null
     * @Assert\NotBlank()
     * @ORM\Column(name="Direccion", type="string", length=82, nullable=false)
     */
    private $direccion;

    /**
     * @var string|null
     * @Assert\NotBlank()
     * @ORM\Column(name="Telefono", type="string", length=100, nullable=false)
     */
    private $telefono;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="Tipo_Iva", type="string", length=40, nullable=false)
     */
    private $tipoIva;

    /**
     * @var int
     * @Assert\NotNull()
     * @ORM\Column(name="Documento", type="bigint", nullable=false)
     */
    private $documento;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(?string $direccion): self
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): self
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getTipoIva(): ?string
    {
        return $this->tipoIva;
    }

    public function setTipoIva(string $tipoIva): self
    {
        $this->tipoIva = $tipoIva;

        return $this;
    }

    public function getDocumento(): ?string
    {
        return $this->documento;
    }

    public function setDocumento(string $documento): self
    {
        $this->documento = $documento;

        return $this;
    }


}
