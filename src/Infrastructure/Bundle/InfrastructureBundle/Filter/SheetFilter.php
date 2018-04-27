<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Filter;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\Session\Session;

class SheetFilter
{
    const SHEET_FILTER = 'sheet_filters';

    /**
     * @var Session
     */
    private $session;

    /**
     * SheetFilter constructor.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param Event $event
     *
     * @return array|null
     */
    public function get(Event $event)
    {
        return $this->session->get($this->getKey($event));
    }

    /**
     * @param Event $event
     * @param array $filters
     */
    public function add(Event $event, array $filters)
    {
        $this->session->set($this->getKey($event),
            array_filter($filters, function ($filter) {
                return null !== $filter;
            })
        );
    }

    /**
     *  Clear sheet filters
     *
     * @param Event $event
     */
    public function clear(Event $event)
    {
        $this->session->remove($this->getKey($event));
    }

    /**
     * @param Event $event
     *
     * @return string
     */
    private function getKey(Event $event)
    {
        return sprintf('%s_%s', self::SHEET_FILTER, $event->getId());
    }
}
