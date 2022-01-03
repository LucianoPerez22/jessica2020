<?php

namespace App\Form\Handler;

use App\Entity\User;
use App\Service\RecoverPassword;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use App\Zennovia\Common\BaseFormHandler;
use App\Zennovia\Common\EntityManagerHelper;

class RecoverPasswordFormHandler extends BaseFormHandler
{
    /**
     * @var ObjectRepository
     */
    protected $repository;

    /**
     * @var RecoverPassword
     */
    protected $sendMailHandler;

    /**
     * @var TranslatorInterface;
     */
    protected $translator;

    /**
     * @var User
     */
    protected $user;

    /**
     * RecoverPasswordFormHandler constructor.
     * @param FormFactoryInterface $formFactory
     * @param EntityManagerHelper $entityManagerHelper
     * @param TranslatorInterface $translator
     * @param RecoverPassword $sendMailHandler
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerHelper $entityManagerHelper,
                                TranslatorInterface $translator,
        RecoverPassword $sendMailHandler
    ) {
        $this->repository = $entityManagerHelper->getRespository('User', 'App');
        $this->sendMailHandler = $sendMailHandler;
        $this->translator = $translator;

        parent::__construct($formFactory, $entityManagerHelper);
    }

    /**
     * @param mixed $repository
     * @return $this
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @return ObjectRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param TranslatorInterface $translator
     * @return $this
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param $helper
     * @return $this
     */
    public function setSendMailHandler($helper)
    {
        $this->sendMailHandler = $helper;

        return $this;
    }

    /**
     * @return RecoverPassword
     */
    public function getSendMailHandler()
    {
        return $this->sendMailHandler;
    }

    public function preProcessForm()
    {
        parent::preProcessForm();

        $data = $this->getDataForm();
        $this->user = $this->getRepository()->findOneBy(array('email' => $data['email']));

        if (!$this->user) {
            throw new \Exception($this->getTranslator()->trans('recover_password.message.user_not_found', array(), 'email'));
        }
    }

    public function processForm()
    {
        try {
            $this->preProcessForm();

            $email = $this->user->getEmail();
            $hash = $this->generateHash($email);
            $this->user->setHash($hash);

            $data = ['email' => $email, 'user' => $this->user->getFullName(), 'hash' => $hash];

            $sendMailHelper= $this->getSendMailHandler();
            $sendMailHelper->setSubject($this->getTranslator()->trans('recover_password.message.subject', array(), 'email'));

            if (!$sendMailHelper->sendMail($data)) {
                $this->user->setHash(null);
                throw new \Exception($this->getTranslator()->trans('recover_password.message.send_error', array(), 'email'));
            }


            $this->getEntityManagerHelper()->doSave($this->user);

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
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
