<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\EventListener;

use Proximum\Vimeet\Application\Event\TraceEvent;
use Proximum\Vimeet\Domain\Model\Trace;
use Proximum\Vimeet\Domain\Repository\TraceRepositoryInterface;

class TraceEventListener
{
    /**
     * @var TraceRepositoryInterface
     */
    private $traceRepository;

    /**
     * @param TraceRepositoryInterface $traceRepository
     */
    public function __construct(TraceRepositoryInterface $traceRepository)
    {
        $this->traceRepository = $traceRepository;
    }

    /**
     * @param TraceEvent $event
     */
    public function trace(TraceEvent $event)
    {
        $trace = new Trace(
            $event->getTraceable(),
            $event->getAction(),
            $event->getUser(),
            $event->getDate(),
            $event->getComment()
        );

        $this->traceRepository->add($trace);
    }
}
