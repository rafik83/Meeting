<?php

namespace Proximum\Vimeet\Application\Components\Mail;

class AbstractMail
{
    /**
     * @var string
     */
    protected $subject;

    /**
     * @var array
     */
    protected $subjectParameters = [];

    /**
     * @var string
     */
    protected $template;

    /**
     * @var string
     */
    protected $messageId;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = false;

    /**
     * @var string
     */
    private $sender;

    /**
     * @var array of receivers email
     */
    private $receivers;

    /**
     * @var array of receivers in Cc
     */
    private array $receiversCc = [];

    /**
     * AbstractMail constructor.
     *
     * @param string $sender
     * @param string $email
     * @param string $locale
     */
    public function __construct($sender, $email, $locale)
    {
        $this->sender      = $sender;
        $this->receivers[] = $email;
        $this->locale      = $locale;
    }

    /**
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param string $email
     */
    public function addReceiver($email)
    {
        if (false === in_array($email, $this->receivers)) {
            $this->receivers[] = $email;
        }
    }

    public function addReceiverCc(string $email): void
    {
        if (false === in_array($email, $this->receiversCc)) {
            $this->receiversCc[] = $email;
        }
    }

    /**
     * @return array of receivers email
     */
    public function getReceivers()
    {
        return $this->receivers;
    }

    public function getReceiversCc(): array
    {
        return $this->receiversCc;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return array
     */
    public function getSubjectParameters()
    {
        return $this->subjectParameters;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return bool
     */
    public function sendToEmailTeam()
    {
        return $this->sendToEmailTeam;
    }

    public function hasToTranslateSubject(): bool
    {
        return true;
    }
}
