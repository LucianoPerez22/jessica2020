<?php

namespace App\Form\Handler;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Zennovia\Common\BaseFormHandler;
use App\Zennovia\Common\EntityManagerHelper;
use App\Service\Registration;

class SaveUserFormHandler extends BaseFormHandler
{
    protected $sendMailHandler;
    protected $translator;

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerHelper $entityManagerHelper,
                                TranslatorInterface $translator,
        Registration $sendMailHandler
    ) {
        $this->translator = $translator;
        $this->sendMailHandler = $sendMailHandler;
        parent::__construct($formFactory, $entityManagerHelper);
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    public function setMailHandler($sendMailHandler)
    {
        $this->sendMailHandler = $sendMailHandler;

        return $this;
    }

    public function getSendMailHandler()
    {
        return $this->sendMailHandler;
    }

    protected function setHashForEmail()
    {
        $hash = $this->generateHash($this->model->getEmail());
        $this->model->setHash($hash);
    }

    public function preProcessForm()
    {
        parent::preProcessForm();

        $this->setHashForEmail();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function postProcessForm()
    {
        $email = $this->getModel()->getEmail();

        if (is_null($email)) {
            throw new \Exception($this->getTranslator()->trans('registration.message.email_empty', array(), 'email'));
        }

        $data = ['email' => $email, 'hash' => $this->getModel()->getHash()];

        $registration = $this->getSendMailHandler();
        $registration->setSubject($this->getTranslator()->trans('registration.message.subject', array(), 'email'));
        if ($registration->sendMail($data)) {
            return true;
        }

        throw new \Exception($this->getTranslator()->trans('registration.message.send_error', array(), 'email'));
    }

    protected function generateHash($email)
    {
        $code = md5(sprintf(
            '%s_%d_%f_%s_%d',
            uniqid(),
            rand(0, 99999),
            microtime(true),
            $email,
            rand(99999, 999999)
        ));

        return $code;
    }
}
