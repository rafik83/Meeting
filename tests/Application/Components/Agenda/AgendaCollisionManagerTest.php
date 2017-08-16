<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Components\Agenda;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Agenda\AgendaCollisionManager;
use Proximum\Vimeet\Application\View\Agenda\MassUnavailabilityView;
use Proximum\Vimeet\Application\View\Agenda\MeetingView;
use Proximum\Vimeet\Application\View\Agenda\UnavailabilityView;

class AgendaCollisionManagerTest extends TestCase
{
    /** @var AgendaCollisionManager */
    private $agendaCollisionManager;

    public function setUp()
    {
        $this->agendaCollisionManager = new AgendaCollisionManager();
    }

    public function testHandleCollision()
    {
        $beginHappening1 = new \DateTime('2016-10-12 12:00:00');
        $beginHappening2 = new \DateTime('2016-10-12 16:00:00');
        $endHappening1 = new \DateTime('2016-10-12 15:30:00');
        $endHappening2 = new \DateTime('2016-10-12 17:30:00');
        $meetingView = new MeetingView(
            'userSheetTitle',
            2,
            'title',
            $beginHappening1,
            $endHappening1,
            'ref',
            'Europe/Paris',
            'leftColor',
            'rightColor',
            []
        );

        $massView = new MassUnavailabilityView(
            1,
            $beginHappening2,
            $endHappening2,
            'title',
            'description',
            'picto',
            'leftColor',
            'rightColor',
            'Europe/Paris',
            false
        );

        $unavailabilityView = new UnavailabilityView(1, $beginHappening2, $endHappening2, 'Europe/Paris');

        $expected = [[$meetingView], [], [$unavailabilityView], []];

        $result = $this->agendaCollisionManager->handleCollision([$meetingView], [], [$unavailabilityView], [$massView]);

        $this->assertEquals($expected, $result);
    }
}
