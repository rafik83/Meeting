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
use Proximum\Vimeet\Application\View\Agenda\HappeningView;
use Proximum\Vimeet\Application\View\Agenda\MassUnavailabilityView;
use Proximum\Vimeet\Application\View\Agenda\Meeting\MeetingOwnSheetParticipantView;
use Proximum\Vimeet\Application\View\Agenda\MeetingView;
use Proximum\Vimeet\Application\View\Agenda\SheetMetView;
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
            1,
            'userSheetTitle',
            2,
            [new SheetMetView('Sheet title', false)],
            [new MeetingOwnSheetParticipantView('Korben', 'Dallas')],
            $beginHappening1,
            $endHappening1,
            10,
            8,
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

        $unavailabilityView = new UnavailabilityView(
            1,
            $beginHappening2,
            $endHappening2,
            'Europe/Paris',
            null,
            true,
            true
        );

        $expected = [[$meetingView], [], [$unavailabilityView], []];

        $result = $this
            ->agendaCollisionManager
            ->handleCollision([$meetingView], [], [$unavailabilityView], [$massView]);

        $this->assertEquals($expected, $result);
    }

    public function testCollision()
    {
        $massView1 = $this->prophesize(MassUnavailabilityView::class); // should stay
        $massView2 = $this->prophesize(MassUnavailabilityView::class); // should collide with Unavailability
        $massView3 = $this->prophesize(MassUnavailabilityView::class); // should collide with massView1
        $massView4 = $this->prophesize(MassUnavailabilityView::class); // should collide with happening
        $massView5 = $this->prophesize(MassUnavailabilityView::class); // should stay between happening
        $massView6 = $this->prophesize(MassUnavailabilityView::class); // should collide with meeting

        $reflection = new \ReflectionClass(MassUnavailabilityView::class);
        $reflectionProperty = $reflection->getProperty('isBlocking');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($massView1, true);
        $reflectionProperty->setValue($massView2, true);
        $reflectionProperty->setValue($massView3, false);
        $reflectionProperty->setValue($massView4, false);
        $reflectionProperty->setValue($massView5, false);
        $reflectionProperty->setValue($massView6, false);
        $reflectionProperty->setAccessible(false);

        $unavailabilityView1 = $this->prophesize(UnavailabilityView::class);
        $unavailabilityView2 = $this->prophesize(UnavailabilityView::class);

        $happeningView1 = $this->prophesize(HappeningView::class);
        $happeningView2 = $this->prophesize(HappeningView::class);
        $happeningView3 = $this->prophesize(HappeningView::class);

        $meetingView1 = $this->prophesize(MeetingView::class);
        $meetingView2 = $this->prophesize(MeetingView::class);

        $beginU1 = new \DateTime('2017-10-10 10:00:00');
        $beginU2 = new \DateTime('2017-10-10 11:00:00');
        $endU1 = new \DateTime('2017-10-10 10:30:00');
        $endU2 = new \DateTime('2017-10-10 11:30:00');

        $beginH1 = new \DateTime('2017-10-10 14:00:00');
        $beginH2 = new \DateTime('2017-10-10 15:00:00');
        $beginH3 = new \DateTime('2017-10-10 16:00:00');
        $endH1 = new \DateTime('2017-10-10 14:30:00');
        $endH2 = new \DateTime('2017-10-10 15:30:00');
        $endH3 = new \DateTime('2017-10-10 16:30:00');

        $beginM1 = new \DateTime('2017-10-10 17:00:00');
        $beginM2 = new \DateTime('2017-10-10 18:00:00');
        $endM1 = new \DateTime('2017-10-10 17:30:00');
        $endM2 = new \DateTime('2017-10-10 18:30:00');

        $unavailabilityView1->getBegin()->willReturn($beginU1);
        $unavailabilityView1->getEnd()->willReturn($endU1);
        $unavailabilityView2->getBegin()->willReturn($beginU2);
        $unavailabilityView2->getEnd()->willReturn($endU2);

        $happeningView1->getBegin()->willReturn($beginH1);
        $happeningView2->getBegin()->willReturn($beginH2);
        $happeningView3->getBegin()->willReturn($beginH3);
        $happeningView1->getEnd()->willReturn($endH1);
        $happeningView2->getEnd()->willReturn($endH2);
        $happeningView3->getEnd()->willReturn($endH3);

        $meetingView1->getBegin()->willReturn($beginM1);
        $meetingView2->getBegin()->willReturn($beginM2);
        $meetingView1->getEnd()->willReturn($endM1);
        $meetingView2->getEnd()->willReturn($endM2);

        $beginMass1 = new \DateTime('2017-10-10 12:10:00');
        $endMass1 = new \DateTime('2017-10-10 12:45:00');
        $beginMass2 = new \DateTime('2017-10-10 10:20:00');
        $endMass2 = new \DateTime('2017-10-10 10:45:00');
        $beginMass3 = new \DateTime('2017-10-10 12:15:00');
        $endMass3 = new \DateTime('2017-10-10 12:55:00');
        $beginMass4 = new \DateTime('2017-10-10 14:25:00');
        $endMass4 = new \DateTime('2017-10-10 14:55:00');
        $beginMass5 = new \DateTime('2017-10-10 14:35:00');
        $endMass5 = new \DateTime('2017-10-10 14:55:00');
        $beginMass6 = new \DateTime('2017-10-10 18:00:00');
        $endMass6 = new \DateTime('2017-10-10 18:35:00');

        $massView1->getBegin()->willReturn($beginMass1);
        $massView2->getBegin()->willReturn($beginMass2);
        $massView3->getBegin()->willReturn($beginMass3);
        $massView4->getBegin()->willReturn($beginMass4);
        $massView5->getBegin()->willReturn($beginMass5);
        $massView6->getBegin()->willReturn($beginMass6);
        $massView1->getEnd()->willReturn($endMass1);
        $massView2->getEnd()->willReturn($endMass2);
        $massView3->getEnd()->willReturn($endMass3);
        $massView4->getEnd()->willReturn($endMass4);
        $massView5->getEnd()->willReturn($endMass5);
        $massView6->getEnd()->willReturn($endMass6);

        $meetingViews = [$meetingView1->reveal(), $meetingView2->reveal()];
        $happeningViews = [$happeningView1->reveal(), $happeningView2->reveal(), $happeningView3->reveal()];
        $unavailabilityViews = [$unavailabilityView1->reveal(), $unavailabilityView2->reveal()];
        $massViews =  [
            0 => $massView1->reveal(),
            1 => $massView2->reveal(),
            2 => $massView3->reveal(),
            3 => $massView4->reveal(),
            4 => $massView5->reveal(),
            5 => $massView6->reveal(),
        ];
        $expectedMassViews = [
            0 => $massView1->reveal(),
            4 => $massView5->reveal(),
        ];

        $expected = [$meetingViews, $happeningViews, $unavailabilityViews, $expectedMassViews];

        $result = $this
            ->agendaCollisionManager
            ->handleCollision($meetingViews, $happeningViews, $unavailabilityViews, $massViews);

        $this->assertEquals($expected, $result);
    }
}
