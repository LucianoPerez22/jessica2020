<?php

namespace App\Service;

use Symfony\Component\Templating\EngineInterface;
use App\Zennovia\Common\BaseSendMailHandler;
use Twig\Environment;

class RecoverPassword extends BaseSendMailHandler
{
    protected $template = "email/recover_password.html.twig";

    public function __construct(Environment $templating, \Swift_Mailer $mailer)
    {
        parent::__construct($templating, $mailer);
    }

    public function sendMail($data)
    {
        $this->setTo($data['email']);
        $this->setData($data);

        return $this->createMessageAndSendMail();
    }
}
