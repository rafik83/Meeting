<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

use OpenTok\Session;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Adapter\VideoConferenceInterface;
use Proximum\Vimeet\Application\Command\VideoConference\RequestAccess;
use Proximum\Vimeet\Application\Command\VideoConference\RequestAccessHandler;
use Proximum\Vimeet\Application\View\Meeting\VideoConferenceView;
use Proximum\Vimeet\Domain\Model\VideoConference;
use Proximum\Vimeet\Domain\Model\VideoConferenceToken;
use Proximum\Vimeet\Domain\Repository\VideoConferenceRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\MeetingFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class RequestAccessHandlerTest extends TestCase
{
    public function testHandleWithoutExistingVideoConference()
    {
        $meeting = MeetingFactory::createMeeting();
        $user    = UserFactory::create();

        // Mock
        $session                   = $this->prophesize(Session::class);
        $videoConference           = $this->prophesize(VideoConferenceAdapterInterface::class);
        $videoConferenceRepository = $this->prophesize(VideoConferenceRepositoryInterface::class);

        $session->getSessionId()->shouldBeCalled()->willReturn('T1==cGFydG5lcl9pZD00NTkyNjE2MiZzaWc9');
        $videoConference->getApiKey()->shouldBeCalled()->willReturn('API_KEY');

        $videoConferenceRepository->findByMeeting($meeting)
            ->shouldBeCalled()
            ->willReturn(null);

        $videoConference->createSession()
            ->shouldBeCalled()
            ->willReturn($session->reveal());

        $videoConference->generateAccessToken($session->reveal(), $meeting->getSlot())
            ->shouldBeCalled()
            ->willReturn('TOKEN');

        $videoConferenceRepository->add(new VideoConference('T1==cGFydG5lcl9pZD00NTkyNjE2MiZzaWc9', $meeting))
            ->shouldBeCalled();

        $handler = new RequestAccessHandler(
            $videoConference->reveal(),
            $videoConferenceRepository->reveal()
        );

        // Expected
        $expectedVideoConferenceView = new VideoConferenceView(
            'TOKEN',
            'T1==cGFydG5lcl9pZD00NTkyNjE2MiZzaWc9',
            'API_KEY'
        );

        $videoConferenceView = $handler->handle(new RequestAccess($meeting, $user));

        $this->assertEquals($expectedVideoConferenceView, $videoConferenceView);
    }

    public function testHandleWithExistingVideoConference()
    {
        $meeting = MeetingFactory::createMeeting();
        $user    = UserFactory::create();

        // Mock
        $videoConference           = $this->prophesize(VideoConference::class);
        $session                   = $this->prophesize(Session::class);
        $videoConferenceInterface  = $this->prophesize(VideoConferenceAdapterInterface::class);
        $videoConferenceRepository = $this->prophesize(VideoConferenceRepositoryInterface::class);

        $videoConference->getTokenByUser($user)->willReturn(null);
        $videoConference->getSessionId()->willReturn('T1==cGFydG5lcl9pZD00NTkyNjE2MiZzaWc9');
        $videoConferenceInterface->getApiKey()->shouldBeCalled()->willReturn('API_KEY');

        $videoConferenceRepository->findByMeeting($meeting)
            ->shouldBeCalled()
            ->willReturn($videoConference->reveal());

        $videoConferenceInterface->getSession('T1==cGFydG5lcl9pZD00NTkyNjE2MiZzaWc9')
            ->shouldBeCalled()
            ->willReturn($session->reveal());

        $videoConferenceInterface->generateAccessToken($session->reveal(), $meeting->getSlot())
            ->shouldBeCalled()
            ->willReturn('TOKEN');

        $videoConference->setToken(
            new VideoConferenceToken(
                $videoConference->reveal(),
                $user,
                'TOKEN'
            )
        )->shouldBeCalled();

        $videoConferenceRepository->set($videoConference)->shouldBeCalled();

        $handler = new RequestAccessHandler(
            $videoConferenceInterface->reveal(),
            $videoConferenceRepository->reveal()
        );

        // Expected
        $expectedVideoConferenceView = new VideoConferenceView(
            'TOKEN',
            'T1==cGFydG5lcl9pZD00NTkyNjE2MiZzaWc9',
            'API_KEY'
        );

        $videoConferenceView = $handler->handle(new RequestAccess($meeting, $user));

        $this->assertEquals($expectedVideoConferenceView, $videoConferenceView);
    }
}
