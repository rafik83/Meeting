<?php

namespace Proximum\Vimeet\Domain\Model\Transactional\Mail;

class MessageTranslation
{
    /** @var int|null */
    private $id;

    /** @var string */
    private $subject;

    /** @var string */
    private $content;

    /** @var string */
    private $locale;

    /** @var Message */
    private $message;

    /**
     * @param string  $subject
     * @param string  $content
     * @param string  $locale
     * @param Message $message
     */
    public function __construct(
        string $subject,
        string $content,
        string $locale,
        Message $message
    ) {
        $this->subject   = $subject;
        $this->content   = $content;
        $this->locale    = $locale;
        $this->message   = $message;
    }

    /** @return int|null */
    public function getId(): ?int
    {
        return $this->id;
    }

    /** @return string */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /** @return string */
    public function getContent(): string
    {
        return $this->content;
    }

    /** @return string */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /** @return Message */
    public function getMessage(): Message
    {
        return $this->message;
    }

    /**
     * @param string $subject
     * @param string $content
     */
    public function set(string $subject, string $content): void
    {
        $this->subject = $subject;
        $this->content = $content;
    }
}
