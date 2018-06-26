<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Badge;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Badge\GetUserBadgeByEventQuery;
use Proximum\Vimeet\Application\Query\Badge\GetUserBadgeByEventQueryHandler;
use Proximum\Vimeet\Application\Query\Badge\UserBadgeByEventView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Badge\ShowAction;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class ShowActionTest extends TestCase
{
    public function testShowBadge()
    {
        $event = $this->prophesize(Event::class);

        $eventDomain = $this->prophesize(EventDomain::class);
        $eventDomain->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        $request = $this->prophesize(Request::class);

        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getUser()->willReturn($user->reveal());

        $userBadgeByEventView = new UserBadgeByEventView(
            'Sheet title',
            'Korben',
            'Dallas',
            'Taxi driver',
            'Exhibitor',
            '000420001337',
            'qrCodeImageBase64',
            '/path/to/header.png'
        );

        $queryBus = $this->prophesize(QueryBusInterface::class);
        $queryBus
            ->handle(new GetUserBadgeByEventQuery($event->reveal(), $user->reveal()))
            ->shouldBeCalled()
            ->willReturn($userBadgeByEventView)
        ;

        $engine = $this->prophesize(EngineInterface::class);
        $engine
            ->render('EventBundle:Badge:show.html.twig',
                [
                    'event' => $event->reveal(),
                    'sheet' => $sheet->reveal(),
                    'userBadgeByEventView' => $userBadgeByEventView,
                ]
            )
            ->shouldBeCalled()
            ->willReturn('HTML of the badge')
        ;

        $authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $authorizationChecker->isGranted(SheetVoter::EDIT, $sheet->reveal())->shouldBeCalled()->willReturn(true);

        $showAction = new ShowAction(
            $authorizationChecker->reveal(),
            $engine->reveal(),
            $queryBus->reveal()
        );
        $response = $showAction($request->reveal(), $eventDomain->reveal(), $sheet->reveal(), $participant->reveal());

        $this->assertInstanceOf(Response::class, $response);
    }
}
