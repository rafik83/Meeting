<?php

namespace Proximum\Vimeet\Tests\Application\Query\Badge;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\Query\Badge\GetUserBadgeAndPlanningByEventQuery;
use Proximum\Vimeet\Application\Query\Badge\GetUserBadgeAndPlanningByEventQueryHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Badge\GetUserBadgeByEventQuery;
use Proximum\Vimeet\Application\Query\Badge\UserBadgeAndPlanningByEventView;
use Proximum\Vimeet\Application\Query\Badge\UserBadgeByEventView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class GetUserBadgeAndPlanningByEventQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $user = $this->prophesize(User::class);
        $user->getLocale()->shouldBeCalled()->willReturn('fr');

        $event = $this->prophesize(Event::class);
        $event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('en');

        $userBadgeByEventView = new UserBadgeByEventView(
            'Taxi company',
            'Korben',
            'Dallas',
            'Taxi Driver',
            'Exhibitor',
            '0000133700042',
            'data:qrCodeImageBase64',
            '/path/to/header.png',
            '#ffffff',
            '#000000',
            'France',
            false,
            null,
            null,
            false,
            '#eee',
            '#000'
        );

        $queryBus = $this->prophesize(QueryBusInterface::class);
        $queryBus
            ->handle(new GetUserBadgeByEventQuery($event->reveal(), $user->reveal()))
            ->shouldBeCalled()
            ->willReturn($userBadgeByEventView)
        ;

        $participantPlanningFormatter = $this->prophesize(ParticipantPlanningFormatter::class);
        $participantPlanningFormatter
            ->formatPlanningFromUserAndEventWithUnallocated(
                $user->reveal(),
                $event->reveal(),
                'en'
            )
            ->shouldBeCalled()
            ->willReturn('User planning')
        ;

        $getUserBadgeAndPlanningByEventQueryHandler = new GetUserBadgeAndPlanningByEventQueryHandler(
            $queryBus->reveal(),
            $participantPlanningFormatter->reveal()
        );
        $result = $getUserBadgeAndPlanningByEventQueryHandler->handle(
            new GetUserBadgeAndPlanningByEventQuery($event->reveal(), $user->reveal())
        );

        $this->assertEquals(new UserBadgeAndPlanningByEventView($userBadgeByEventView, 'User planning'), $result);
    }
}
