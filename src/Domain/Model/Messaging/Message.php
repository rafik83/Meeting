<?php

namespace Proximum\Vimeet\Domain\Model\Messaging;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MessageInterface;

class Message implements MessageInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * @var string
     */
    private static $template = 'MailBundle:Mail:Messaging/emailing.html.twig';

    /**
     * Flag to send email to as well
     *
     * @var bool
     */
    private $sendToEmailTeam = false;

    /**
     * Flag to send email to billing info as well
     *
     * @var bool
     */
    private $sendEmailToBillingInfo = false;

    /**
     * @param Event              $event
     * @param \DateTimeInterface $createdAt
     * @param string             $name
     * @param bool               $sendToEmailTeam
     * @param bool               $sendEmailToBillingInfo
     */
    public function __construct(
        Event $event,
        \DateTimeInterface $createdAt,
        $name,
        $sendToEmailTeam = false,
        $sendEmailToBillingInfo = false
    ) {
        $this->event                  = $event;
        $this->name                   = $name;
        $this->createdAt              = $createdAt;
        $this->translations           = new ArrayCollection();
        $this->sendToEmailTeam        = $sendToEmailTeam;
        $this->sendEmailToBillingInfo = $sendEmailToBillingInfo;
    }

    /**
     * @param string $name
     *
     * @return Message $this
     */
    public function update($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string             $locale
     * @param string             $subject
     * @param string             $content
     * @param \DateTimeInterface $dateTime
     *
     * @return Message
     */
    public function translate($locale, $subject, $content, \DateTimeInterface $dateTime)
    {
        if ($this->hasTranslation($locale)) {
            $this->getTranslation($locale)->set($locale, $subject, $content);
        } else {
            $this->setTranslation($locale, $subject, $content, $dateTime);
        }

        return $this;
    }

    /**
     * @param string             $locale
     * @param string             $subject
     * @param string             $content
     * @param \DateTimeInterface $dateTime
     */
    public function setTranslation($locale, $subject, $content, \DateTimeInterface $dateTime)
    {
        $this->translations->set(
            $locale,
            new MessageTranslation(
                $subject,
                $content,
                $locale,
                $this,
                $dateTime
            )
        );
    }

    /**
     * @param string $locale
     *
     * @return MessageTranslation|null
     */
    public function getTranslation($locale)
    {
        return $this->translations->get($locale);
    }

    /**
     * @return MessageTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations->toArray();
    }

    /**
     * @param string $locale
     *
     * @return bool
     */
    public function hasTranslation($locale)
    {
        return $this->translations->containsKey($locale);
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get translation subject
     *
     * @param string $locale
     *
     * @return string
     */
    public function getSubject($locale): string
    {
        return $this->hasTranslation($locale) ? $this->getTranslation($locale)->getSubject() : '';
    }

    /**
     * Get translation content
     *
     * @param string $locale
     *
     * @return string
     */
    public function getContent($locale): string
    {
        return $this->hasTranslation($locale) ? $this->getTranslation($locale)->getContent() : '';
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return self::$template;
    }

    /**
     * @return bool
     */
    public function isSendToEmailTeam()
    {
        return $this->sendToEmailTeam;
    }

    /**
     * @return bool
     */
    public function isSendEmailToBillingInfo()
    {
        return $this->sendEmailToBillingInfo;
    }
}
