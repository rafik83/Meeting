<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Visio\Test;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\VideoConference\RequestTestAccess;
use Proximum\Vimeet\Application\Exception\VideoConference\InvalidTokenGeneratorArgumentsException;
use Proximum\Vimeet\Application\View\Meeting\VideoConferenceView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Visio\Test\TestNetworkSessionAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class TestNetworkSessionActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $commandBus, $engine, $authorizationCheckerAdapter, $request, $user, $event, $sheet;

    /** @var UserDomain */
    private $userDomain;

    /** @var EventDomain */
    private $eventDomain;

    public function setUp(): void
    {
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->engine = $this->prophesize(EngineInterface::class);
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->request = $this->prophesize(Request::class);
        $this->user = $this->prophesize(User::class);
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);

        $this->eventDomain = new EventDomain($this->event->reveal());
        $this->userDomain = new UserDomain($this->user->reveal());
    }

    public function testInvokeAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(false)
        ;
        $this->commandBus
            ->handle(Argument::any())
            ->shouldNotBeCalled()
        ;
        $this->engine
            ->render('EventBundle:VideoConference:testNetworkAudioVideo.html.twig', Argument::any())
            ->shouldNotBeCalled()
        ;

        $action = new TestNetworkSessionAction(
            $this->commandBus->reveal(),
            $this->engine->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $action(
            $this->request->reveal(),
            $this->eventDomain,
            $this->userDomain,
            'session_id',
            $this->sheet->reveal()
        );
    }

    public function testInvokeNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->request->getLocale()->shouldBeCalled()->willReturn('fr');
        $this->event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('fr');
        $this->commandBus
            ->handle(
                new RequestTestAccess(
                    $this->event->reveal(),
                    'session_id',
                    'fr'
                )
            )->shouldBeCalled()
            ->willThrow(InvalidTokenGeneratorArgumentsException::class)
        ;

        $this->engine
            ->render('EventBundle:VideoConference:testNetworkAudioVideo.html.twig', Argument::any())
            ->shouldNotBeCalled()
        ;

        $action = new TestNetworkSessionAction(
            $this->commandBus->reveal(),
            $this->engine->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $action(
            $this->request->reveal(),
            $this->eventDomain,
            $this->userDomain,
            'session_id',
            $this->sheet->reveal()
        );
    }

    public function testInvoke(): void
    {
        $this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->request->getLocale()->shouldBeCalled()->willReturn('fr');
        $this->event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('fr');

        $view = new VideoConferenceView(
            'token',
            'session_id',
            'api_key',
            null
        );

        $this->commandBus
            ->handle(
                new RequestTestAccess(
                    $this->event->reveal(),
                    'session_id',
                    'fr'
                )
            )->shouldBeCalled()
            ->willReturn($view)
        ;

        $this->engine
            ->render('EventBundle:VideoConference:testNetworkAudioVideo.html.twig', [
                'sheet' => $this->sheet->reveal(),
                'event' => $this->event->reveal(),
                'videoConferenceView' => $view,
            ])
            ->shouldBeCalled()
            ->willReturn('html')
        ;

        $action = new TestNetworkSessionAction(
            $this->commandBus->reveal(),
            $this->engine->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $action(
            $this->request->reveal(),
            $this->eventDomain,
            $this->userDomain,
            'session_id',
            $this->sheet->reveal()
        );
    }
}
