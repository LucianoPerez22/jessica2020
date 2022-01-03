<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;
use App\Zennovia\Common\FormModelInterface;

/**
 * RegistrationModel
 */
class ResetPasswordModel implements FormModelInterface
{

    /**
     * @var string
     *
     * @Assert\NotBlank(message="You must provide a new password")
     */
    private $password;


    public function saveData($user)
    {
        $user->setHash(null);
    }


    /**
     * Set password
     *
     * @param string $password
     * @return ResetPasswordModel
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
