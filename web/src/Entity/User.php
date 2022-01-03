<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Validator\Constraints\ValidateDependentFields as AppAssert;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"username"})
 * @UniqueEntity(fields={"email"})
 * @AppAssert(
 *     field="superAdmin",
 *     dependentField="groups",
 *     dependentValues={false},
 *     errorPath="groups",
 *     message="custom.validator.empty_value"
 *     )
 */
class User implements UserInterface, \Serializable, EquatableInterface
{
    const ROLE_USER = 'ROLE_USER';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_ALLOWED_TO_SWITCH = 'ROLE_ALLOWED_TO_SWITCH';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=255, nullable=true)
     */
    private $salt;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     * @Assert\NotNull()
     */
    private $enabled = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="super_admin", type="boolean")
     * @Assert\NotNull()
     *
     */
    private $superAdmin = false;

    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=255, nullable=true)
     */
    private $hash;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Group", cascade={"persist","remove"})
     * @ORM\JoinTable(name="user__app_group",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="app_group_id", referencedColumnName="id")}
     *      )
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $groups;

 
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName().' '.$this->getLastName();
    }

    public function getFullName()
    {
        return $this->getName().' '.$this->getLastName();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set salt
     *
     * @param string $salt
     *
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
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
     * Set lastName
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set superAdmin
     *
     * @param boolean $superAdmin
     *
     * @return User
     */
    public function setSuperAdmin($superAdmin)
    {
        $this->superAdmin = $superAdmin;

        return $this;
    }

    /**
     * Get superAdmin
     *
     * @return bool
     */
    public function getSuperAdmin()
    {
        return $this->superAdmin;
    }

    /**
     * Get superAdmin
     *
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->superAdmin;
    }

    /**
     * Set hash
     *
     * @param string $hash
     *
     * @return User
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Get lastLogin
     *
     * @return \Datetime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Set lastLogin
     *
     * @param \Datetime $lastLogin
     *
     * @return User
     */
    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * Set groups
     *
     * @param ArrayCollection $groups
     *
     * @return User
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * Get groups
     *
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /*** PARA LOGIN ***/
    public function getRoles()
    {
        $roles = array(static::ROLE_USER);
        if ($this->getSuperAdmin()) {
            $roles[] = static::ROLE_ADMIN;
            $roles[] = static::ROLE_ALLOWED_TO_SWITCH;
        }
        $groups = $this->getGroups();
        foreach ($groups as $group) {
            $roles = array_merge($roles, $group->getArrayNameRoles());
        }

        $roles = array_unique($roles);

        return $roles;
    }

    public function isAccountNonExpired()
    {
        return $this->isEnabled();
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function eraseCredentials()
    {
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->enabled
        ));
    }

    /**
     * @see \Serializable::unserialize()
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->password,
            $this->enabled
        ) = unserialize($serialized);
    }

    public function isEqualTo(UserInterface $user)
    {
        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    ////----- FIN PARA LOGIN -----////

    /**
     * Indica si un usuario tiene algunos de los roles que se pasan por porametro
     *
     * @param array $roles
     * @return boolean
     */
    public function hasRole(array $roles)
    {
        if ($this->getSuperAdmin()) {
            return true;
        }
        $userRoles = $this->getRoles();
        foreach ($roles as $role) {
            if (in_array(strtoupper($role), $userRoles, true)) {
                return true;
            }
        }

        return false;
    }

    public function getUserIdentifier(){
        return 'username';
    }
}
