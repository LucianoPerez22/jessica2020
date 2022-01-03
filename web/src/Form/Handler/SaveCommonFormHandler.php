<?php

namespace App\Form\Handler;

use App\Service\AddCustomFieldsHelper;
use Symfony\Component\Form\FormFactoryInterface;
use App\Zennovia\Common\BaseFormHandler;
use App\Zennovia\Common\EntityManagerHelper;

class SaveCommonFormHandler extends BaseFormHandler
{
    private $acHelper;

    public function __construct(FormFactoryInterface $formFactory, EntityManagerHelper $entityManagerHelper)
    {
        parent::__construct($formFactory, $entityManagerHelper);
    }

    public function postProcessForm()
    {
        if ($this->form->has("custom_fields")) {
            $fields = $this->form->get("custom_fields")->all();
            $record = ($this->getEntity()) ?? $this->getModel(); //@NOTE Si hay un entity quiere decir que el form tiene un model
        }
    }
}
