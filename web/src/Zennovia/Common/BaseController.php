<?php

namespace App\Zennovia\Common;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Custom Controller para poder sobreescribir metodos generales.
 */
class BaseController extends AbstractController
{
    protected $dataReturn = Array();

    protected function addFlashSuccess($message)
    {
        $this->addFlash('success', $message);

        return $this;
    }

    protected function addFlashInfo($message)
    {
        $this->addFlash('info', $message);

        return $this;
    }

    protected function addFlashError($message)
    {
        $this->addFlash('error', $message);

        return $this;
    }

    protected function addFlashWarning($message)
    {
        $this->addFlash('warning', $message);

        return $this;
    }

    protected function translate($message='',$parameters=array())
    {
        return $this->get('translator')->trans($message, $parameters);
    }


}
