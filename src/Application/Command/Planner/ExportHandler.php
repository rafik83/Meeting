<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Planner;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Command\MeetingRequest\Admin\LockMeetingRequestUpdate;
use Proximum\Vimeet\Application\Command\MeetingRequest\Admin\LockMeetingRequestUpdateHandler;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\Dispatcher;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\DispatcherHandler;
use Proximum\Vimeet\Application\Query\Planner\PlannerViewQuery;
use Proximum\Vimeet\Application\Query\Planner\PlannerViewQueryHandler;
use Proximum\Vimeet\Application\Exception\Planner\SlotNotConfiguredException;
use Proximum\Vimeet\Application\Exception\Planner\DayNotConfiguredException;
use Proximum\Vimeet\Domain\Unavailability\Exception\UnableToDispatchException;

class ExportHandler
{
    const XML_ROOT_NODE = 'MeetingSchedule';

    /** @var LockMeetingRequestUpdateHandler */
    private $lockMeetingRequestHandler;

    /** @var DispatcherHandler */
    private $dispatcherHandler;

    /** @var PlannerViewQueryHandler */
    private $plannerHandler;

    /** @var SerializerAdapterInterface */
    private $serializer;

    /**
     * @param DispatcherHandler               $dispatcherHandler
     * @param LockMeetingRequestUpdateHandler $lockMeetingRequestUpdateHandler
     * @param PlannerViewQueryHandler         $plannerHandler
     * @param SerializerAdapterInterface      $serializer
     */
    public function __construct(
        DispatcherHandler $dispatcherHandler,
        LockMeetingRequestUpdateHandler $lockMeetingRequestUpdateHandler,
        PlannerViewQueryHandler $plannerHandler,
        SerializerAdapterInterface $serializer
    ) {
        $this->lockMeetingRequestHandler = $lockMeetingRequestUpdateHandler;
        $this->dispatcherHandler         = $dispatcherHandler;
        $this->plannerHandler            = $plannerHandler;
        $this->serializer                = $serializer;
    }

    /**
     * @param Export $export
     *
     * @return string
     *
     * @throws SlotNotConfiguredException
     * @throws DayNotConfiguredException
     * @throws UnableToDispatchException
     */
    public function handle(Export $export)
    {
        $this->dispatcherHandler->handle(new Dispatcher($export->event));

        if (true === $export->lockMeetingRequest) {
            $command = new LockMeetingRequestUpdate($export->event);
            $command->lock = true;

            $this->lockMeetingRequestHandler->handle($command);
        }

        $planner = $this->plannerHandler->handle(new PlannerViewQuery($export->event, $export->locale));
        $content = $this->serializer->serialize($planner, 'xml', ['xml_root_node_name' => self::XML_ROOT_NODE]);

        return $content;
    }
}
