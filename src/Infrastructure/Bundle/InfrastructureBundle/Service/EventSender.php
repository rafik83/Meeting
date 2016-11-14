<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service;

use Proximum\Vimeet\Domain\Model\Event;

class EventSender
{
    const MAIL = 'no-reply';

    /**
     * @var string
     */
    private $applicationDomain;

    /**
     * @var string
     */
    private $defaultSender;

    /**
     * EventSender constructor.
     *
     * @param string $applicationDomain
     * @param string $defaultSender
     */
    public function __construct($applicationDomain, $defaultSender)
    {
        $this->applicationDomain = $applicationDomain;
        $this->defaultSender     = $defaultSender;
    }

    /**
     * @param Event $event
     *
     * @return string
     */
    public function generate(Event $event = null)
    {
        if ($event === null) {
            return $this->defaultSender;
        }

        if (preg_match('/' . $this->applicationDomain . '/', $event->getDomain()) === 1) {
            return self::MAIL . '@' . $event->getDomain();
        }

        return $this->defaultSender;
    }
}
