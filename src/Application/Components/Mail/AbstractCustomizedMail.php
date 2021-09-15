<?php

namespace Proximum\Vimeet\Application\Components\Mail;

use Proximum\Vimeet\Domain\Model\Event;

abstract class AbstractCustomizedMail extends AbstractMail
{
    public const TEMPLATE = 'MailBundle:Mail:base_customized.html.twig';

    /** @var Event */
    protected $event;

    /** @var string */
    protected $subject;

    /** @var string */
    protected $content;

    public function __construct(
        Event $event,
        string $sender,
        string $receiver,
        string $locale
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->event = $event;
        $this->subject = '';
        $this->content = '';
    }

    public function getTemplate(): string
    {
        return self::TEMPLATE;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function hasToTranslateSubject(): bool
    {
        return false;
    }
}
