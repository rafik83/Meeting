<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Planner;

use Proximum\Vimeet\Application\Query\Planner\PlannerViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;

class ExportController extends Controller
{
    public function exportAction(Request $request, Event $event)
    {
        $planner = $this->get('tactician.commandbus.query')->handle(
            new PlannerViewQuery($event, $request->getLocale())
        );

        $serializer  = new Serializer(
            [$this->get('serializer_normalizer_planner.planner_normalizer')],
            [new XmlEncoder('MeetingSchedule')]
        );

        $content = $serializer->serialize($planner, 'xml');

        return new Response($content);
    }
}
