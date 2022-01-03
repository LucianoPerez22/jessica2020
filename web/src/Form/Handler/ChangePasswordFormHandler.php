<?php

namespace App\Form\Handler;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Zennovia\Common\BaseFormHandler;
use App\Zennovia\Common\EntityManagerHelper;

class ChangePasswordFormHandler extends BaseFormHandler
{
    private $encoder;

    public function __construct(FormFactoryInterface $formFactory, EntityManagerHelper $entityManagerHelper, UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
        parent::__construct($formFactory, $entityManagerHelper);
    }


    public function setEncoder($encoder)
    {
        $this->encoder = $encoder;

        return $this;
    }

    public function preProcessForm()
    {
        $this->setModel($this->getDataForm());
        $this->encodePassword($this->getModel()->getPassword());
    }

    public function encodePassword($pass)
    {
        $user = $this->getEntity();

        if (is_null($user->getSalt())) {
            $user->setSalt(sha1(uniqid(mt_rand(), true)));
        }

        $encoded = $this->encoder->encodePassword($user, $pass);

        $user->setPassword($encoded);
    }
}
