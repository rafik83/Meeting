<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Messaging;

use Proximum\Vimeet\Domain\Model\Event;

class Message
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
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $content;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @var string
     */
    private static $template = 'MailBundle:Mail:base.html.twig';

    /**
     * @var string
     * @todo dynamic value
     */
    private static $locale = 'fr';

    /**
     * @param Event              $event
     * @param \DateTimeInterface $createdAt
     * @param string             $name
     * @param string             $subject
     * @param string             $content
     */
    public function __construct(Event $event, \DateTimeInterface $createdAt, $name, $subject, $content)
    {
        $this->event     = $event;
        $this->name      = $name;
        $this->subject   = $subject;
        $this->content   = $content;
        $this->createdAt = $createdAt;
    }

    /**
     * @param string $name
     * @param string $subject
     * @param string $content
     */
    public function update($name, $subject, $content)
    {
        $this->name    = $name;
        $this->subject = $subject;
        $this->content = $content;
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
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getTemplate()
    {
        return self::$template;
    }

    public function getLocale()
    {
        return self::$locale;
    }
}
