<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * RolesGroup
 *
 * @ORM\Table(name="app_group")
 * @ORM\Entity(repositoryClass="App\Repository\GroupRepository")
 * @UniqueEntity(fields={"name"})
 */
class Group
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Role")
     * @ORM\JoinTable(name="role__app_group",
     *      joinColumns={@ORM\JoinColumn(name="app_group_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     * @Assert\Count(min=1)
     */
    private $roles;


    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Group
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Group
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set roles
     *
     * @param \stdClass $roles
     * @return Group
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     *
     * @return ArrayCollection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Add roles
     *
     * @param \App\Entity\Role $roles
     * @return Group
     */
    public function addRole(\App\Entity\Role $roles)
    {
        $this->roles[] = $roles;

        return $this;
    }

    /**
     * Remove roles
     *
     * @param \App\Entity\Role $roles
     */
    public function removeRole(\App\Entity\Role $roles)
    {
        $this->roles->removeElement($roles);
    }

    /**
     * Get roles name
     *
     * @return array
     */
    public function getArrayNameRoles()
    {
        $arrayRoles = [];
        if ($this->roles) {
            $roles = $this->roles;
            foreach ($roles as $role) {
                $arrayRoles[] = $role->getName();
            }
        }

        return $arrayRoles;
    }

    /**
     * Get roles titles
     *
     * @return array
     */
    public function getArrayTitleRoles()
    {
        $arrayRoles = [];
        if ($this->roles) {
            $roles = $this->roles;
            foreach ($roles as $role) {
                $arrayRoles[] = $role->getTitle();
            }
        }

        return $arrayRoles;
    }

    /**
     * Get roles description
     *
     * @return array
     */
    public function getArrayDescriptionRoles()
    {
        $arrayRoles = [];
        if ($this->roles) {
            $roles = $this->roles;
            foreach ($roles as $role) {
                $arrayRoles[] = $role->getDescription();
            }
        }

        return $arrayRoles;
    }

    public function getRolesByModule()
    {
        $roles = $this->getRoles();
        $data = [];
        foreach ($roles as $rol) {
            $module = $rol->getModule();
            if (!isset($data[$module])) {
                $data[$module] = [];
            }

            $data[$module][] = $rol;
        }

        return $data;
    }
}
