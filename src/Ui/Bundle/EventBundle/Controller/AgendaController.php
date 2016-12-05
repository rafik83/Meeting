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
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AgendaController extends Controller
{
    /**
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function indexAction(EventDomain $eventDomain)
    {
        return $this->render('EventBundle:Agenda:index.html.twig', [
            'event' => $eventDomain->getEvent(),
            'morning' => [
                $this->getMeeting('conf', new DateTime('2016-12-02 08:30'), new DateTime('2016-12-02 10:15')),
                $this->getMeeting('conf', new DateTime('2016-12-02 08:15'), new DateTime('2016-12-02 09:15')),
                $this->getMeeting('lock', new DateTime('2016-12-02 09:30'), new DateTime('2016-12-02 10:00')),
                $this->getMeeting('lock', new DateTime('2016-12-02 10:30'), new DateTime('2016-12-02 11:00')),
            ],
            'afternoon' => [
                $this->getMeeting('conf', new DateTime('2016-12-02 13:00'), new DateTime('2016-12-02 14:00')),
                $this->getMeeting('conf', new DateTime('2016-12-02 13:30'), new DateTime('2016-12-02 14:30')),
                $this->getMeeting('conf', new DateTime('2016-12-02 13:00'), new DateTime('2016-12-02 14:30')),
                $this->getMeeting('break', new DateTime('2016-12-02 15:00'), new DateTime('2016-12-02 15:45')),
                $this->getMeeting('conf', new DateTime('2016-12-02 16:00'), new DateTime('2016-12-02 16:30')),
            ]
        ]);
    }

    /**
     * Mocking a meeting
     */
    private function getMeeting($type, DateTime $beginHour, DateTime $endHour)
    {
        return [
            'type' => $type,
            'beginHour' => $beginHour,
            'endHour' => $endHour,
            'duration' => $endHour->diff($beginHour),
            'title' => 'Collaboration entre ZODIAC Aerospace / Nexter Robotics',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc molestie euismod nisi, vel rhoncus lacus dignissim non. Curabitur ac justo sed nisi varius congue quis a purus. Suspendisse fermentum commodo mollis. Suspendisse potenti. Praesent dignissim orci sit amet turpis vehicula fermentum.',
            'picture' => '',
            'user' => [
                'name' => 'Pierre Richard ANTONIN BRESSON',
                'job' => 'Response Recherche',
                'picture' => '',
            ],
        ];
    }
}
