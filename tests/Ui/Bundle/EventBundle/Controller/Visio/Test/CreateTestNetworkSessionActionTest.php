<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Visio\Test;

use OpenTok\Session;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Visio\Test\CreateTestNetworkSessionAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CreateTestNetworkSessionActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $videoConferenceAdapter, $router, $authorizationCheckerAdapter, $event;

    public function setUp(): void
    {
        $this->videoConferenceAdapter = $this->prophesize(VideoConferenceAdapterInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->event = $this->prophesize(Event::class);
    }

    public function testInvokeAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter
            ->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new CreateTestNetworkSessionAction(
            $this->videoConferenceAdapter->reveal(),
            $this->router->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $eventDomain = new EventDomain($this->event->reveal());
        $action($eventDomain);
    }

    public function testInvoke(): void
    {
        $this->authorizationCheckerAdapter
            ->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $session = $this->prophesize(Session::class);
        $this->videoConferenceAdapter->createSession()
            ->shouldBeCalled()
            ->willReturn($session->reveal())
        ;

        $this->router->generate('event_video_conference_network_test', ['sessionId' => $session])
            ->shouldBeCalled()
            ->willReturn('/video/network/test/session_id')
        ;

        $action = new CreateTestNetworkSessionAction(
            $this->videoConferenceAdapter->reveal(),
            $this->router->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $eventDomain = new EventDomain($this->event->reveal());
        $result = $action($eventDomain);

        $this->assertEquals('/video/network/test/session_id', $result->getTargetUrl());
    }

    public function testInvokeWithSheet(): void
    {
        $this->authorizationCheckerAdapter
            ->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $session = $this->prophesize(Session::class);
        $this->videoConferenceAdapter->createSession()
            ->shouldBeCalled()
            ->willReturn($session->reveal())
        ;

        $this->router->generate(
            'event_sheet_video_conference_network_test', ['sessionId' => $session, 'sheet' => 12])
            ->shouldBeCalled()
            ->willReturn('/sheet/12/video/network/test/session_id')
        ;

        $action = new CreateTestNetworkSessionAction(
            $this->videoConferenceAdapter->reveal(),
            $this->router->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->shouldBeCalled()->willReturn(12);
        $eventDomain = new EventDomain($this->event->reveal());
        $result = $action($eventDomain, $sheet->reveal());

        $this->assertEquals('/sheet/12/video/network/test/session_id', $result->getTargetUrl());
    }
}
