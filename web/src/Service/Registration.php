<?php

namespace App\Service;

use Symfony\Component\Templating\EngineInterface;
use App\Zennovia\Common\BaseSendMailHandler;
use Twig\Environment;

class Registration extends BaseSendMailHandler
{
    protected $template = "email/registration.html.twig";

    /**
     * Registration constructor.
     * @param EngineInterface $templating
     * @param \Swift_Mailer $mailer
     */
    public function __construct(Environment $templating, \Swift_Mailer $mailer)
    {
        parent::__construct($templating, $mailer);
    }

    /**
     * sendMail
     *
     * @param array $data
     * @return bool|int
     */
    public function sendMail($data)
    {
        $this->setTo($data['email']);
        $this->setData($data);

        return $this->createMessageAndSendMail();
    }
}
