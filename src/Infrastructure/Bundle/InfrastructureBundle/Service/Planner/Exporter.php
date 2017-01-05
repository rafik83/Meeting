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
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Planner\PlannerNormalizer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;

class Exporter
{
    /**
     * @var PlannerViewQueryHandler
     */
    private $plannerHandler;

    /**
     * @var PlannerNormalizer
     */
    private $plannerNormalizer;

    /**
     * @param PlannerViewQueryHandler $plannerHandler
     * @param PlannerNormalizer       $plannerNormalizer
     */
    public function __construct(PlannerViewQueryHandler $plannerHandler, PlannerNormalizer $plannerNormalizer)
    {
        $this->plannerHandler    = $plannerHandler;
        $this->plannerNormalizer = $plannerNormalizer;
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

        $serializer  = new Serializer(
            [$this->plannerNormalizer],
            [new XmlEncoder('MeetingSchedule')]
        );

        $content = $serializer->serialize($planner, 'xml');

        return $content;
    }
}
