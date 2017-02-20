<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\Planner;

use Proximum\Vimeet\Application\Query\Planner\PlannerViewQuery;
use Proximum\Vimeet\Application\Query\Planner\PlannerViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Serializer\Serializer;

class Exporter
{
    /**
     * @var PlannerViewQueryHandler
     */
    private $plannerHandler;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param PlannerViewQueryHandler $plannerHandler
     * @param Serializer              $serializer
     */
    public function __construct(PlannerViewQueryHandler $plannerHandler, Serializer $serializer)
    {
        $this->plannerHandler = $plannerHandler;
        $this->serializer     = $serializer;
    }

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return string
     */
    public function getXML(Event $event, $locale)
    {
        $planner = $this->plannerHandler->handle(new PlannerViewQuery($event, $locale));
        $content = $this->serializer->serialize($planner, 'xml', ['xml_root_node_name' => 'MeetingSchedule']);

        return $content;
    }
}
