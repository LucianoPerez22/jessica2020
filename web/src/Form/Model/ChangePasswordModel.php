<?php

namespace App\Form\Model;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;
use App\Zennovia\Common\FormModelInterface;

class ChangePasswordModel implements FormModelInterface
{
    /**
     * @Assert\NotBlank(message="custom.password.provide_current_password")
     * @SecurityAssert\UserPassword()
     */
    protected $oldPassword;

    /**
     * @Assert\NotBlank(message="custom.password.provide_new_password")
     */
    protected $password;


    /**
     * Setea los atributos del modelo en la entidad
     * que le llega por parámetro
     */
    public function saveData($entity)
    {
        //El password se setea en el handler
    }

    /**
     * Set oldPassword
     *
     * @param string $oldPassword
     * @return ChangePass
     */
    public function setOldPassword($oldPassword)
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    /**
     * Get oldPassword
     *
     * @return string
     */
    public function getOldPassword()
    {
        return $this->oldPassword;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return ChangePass
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
}
