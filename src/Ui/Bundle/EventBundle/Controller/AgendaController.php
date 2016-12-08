<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use DateTime;
use Proximum\Vimeet\Application\Query\Happening\HappeningViewQuery;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AgendaController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function indexAction(Request $request, EventDomain $eventDomain)
    {
        try {
            $happeningListView = $this->get('tactician.commandbus.query')->handle(
                new HappeningViewQuery($eventDomain->getEvent(), $request->getLocale())
            );

            return $this->render('EventBundle:Agenda:index.html.twig', [
                'event'         => $eventDomain->getEvent(),
                'happeningList' => $happeningListView,
            ]);
        } catch (\Exception $exception) {
            return $this->redirectToRoute('event_sheet');
        }
    }

    /**
     * Mocking a meeting
     */
    private function getMeeting($type, DateTime $beginHour, DateTime $endHour)
    {
        return [
            'type'        => $type,
            'beginHour'   => $beginHour,
            'endHour'     => $endHour,
            'duration'    => $endHour->diff($beginHour),
            'title'       => 'Collaboration entre ZODIAC Aerospace / Nexter Robotics',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc molestie euismod nisi, vel rhoncus lacus dignissim non. Curabitur ac justo sed nisi varius congue quis a purus. Suspendisse fermentum commodo mollis. Suspendisse potenti. Praesent dignissim orci sit amet turpis vehicula fermentum.',
            'picture'     => '',
            'user'        => [
                'name'    => 'Pierre Richard ANTONIN BRESSON',
                'job'     => 'Response Recherche',
                'picture' => '',
            ],
        ];
    }
}
