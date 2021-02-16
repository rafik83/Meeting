<?php

namespace Proximum\Vimeet\Domain\Model\Messaging;

class MessageTranslation
{
    /** @var int */
    private $id;

    /** @var string */
    private $subject;

    /** @var string */
    private $content;

    /** @var string */
    private $locale;

    /** @var Message */
    private $message;

    /** @var \DateTimeInterface */
    private $createdAt;

    /**
     * MessageTranslation constructor.
     *
     * @param string             $subject
     * @param string             $content
     * @param string             $locale
     * @param Message            $message
     * @param \DateTimeInterface $createdAt
     */
    public function __construct($subject, $content, $locale, Message $message, \DateTimeInterface $createdAt)
    {
        $this->subject   = $subject;
        $this->content   = $content;
        $this->locale    = $locale;
        $this->message   = $message;
        $this->createdAt = $createdAt;
    }

    /** @return int */
    public function getId()
    {
        return $this->id;
    }

    /** @return string */
    public function getSubject()
    {
        return $this->subject;
    }

    /** @return string */
    public function getContent()
    {
        return $this->content;
    }

    /** @return string */
    public function getLocale()
    {
        return $this->locale;
    }

    /** @return Message */
    public function getMessage()
    {
        return $this->message;
    }

    /** @return \DateTimeInterface */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param string $locale
     * @param string $subject
     * @param string $content
     *
     * @return MessageTranslation
     */
    public function set($locale, $subject, $content)
    {
        $this->locale  = $locale;
        $this->subject = $subject;
        $this->content = $content;

        return $this;
    }
}
