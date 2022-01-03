<?php

namespace App\Zennovia\Common;

use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;

abstract class BaseSendMailHandler
{
    protected $templating;
    protected $mailer;
    protected $from;
    protected $to;
    protected $subject;
    protected $template;
    protected $data = [];

    public function __construct(Environment $templating, \Swift_Mailer $mailer)
    {
        $this->templating = $templating;
        $this->mailer = $mailer;
        $this->message = (new \Swift_Message());
    }

    /**
     * @return EngineInterface
     */
    public function getTemplating()
    {
        return $this->templating;
    }

    /**
     * @param EngineInterface $templating
     */
    public function setTemplating($templating)
    {
        $this->templating = $templating;
    }

    /**
     * @return \Swift_Mailer
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * @param \Swift_Mailer $mailer
     */
    public function setMailer($mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }


    public function createMessageAndSendMail()
    {
        try{
            $this->message->setContentType('text/html')
                ->setSubject($this->getSubject())
                ->setFrom($this->getFrom())
                ->setTo($this->getTo())
                ->setBody(
                    $this->templating->render( $this->getTemplate(), $this->getData())
                );

            return $this->mailer->send($this->message);
        }
        catch(\Exception $e){
            return false;
        }
    }
}
