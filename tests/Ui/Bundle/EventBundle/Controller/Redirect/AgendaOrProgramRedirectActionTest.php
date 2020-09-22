<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Redirect;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AgendaAccessVoter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\HappeningAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Redirect\AgendaOrProgramRedirectAction;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AgendaOrProgramRedirectActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter, $router, $event, $sheet;

    public function setUp(): void
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
    }

    public function testInvokeRedirectToAgenda(): void
    {
        $eventDomain = new EventDomain($this->event->reveal());

        $this->sheet->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->event->getId()->shouldBeCalled()->willReturn(1);
        $this->sheet->getId()->shouldBeCalled()->willReturn(123);

        $this->authorizationCheckerAdapter
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter
            ->isGranted(AgendaAccessVoter::PERMISSION, $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->router->generate(Route::AGENDA_DEFAULT, ['sheet' => 123])
            ->shouldBeCalled()
            ->willReturn('/sheet/123/agenda')
        ;

        $action = new AgendaOrProgramRedirectAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->router->reveal()
        );

        $result = $action($eventDomain, $this->sheet->reveal());

        $this->assertEquals('/sheet/123/agenda', $result->getTargetUrl());
    }

    public function testInvokeRedirectToProgram(): void
    {
        $eventDomain = new EventDomain($this->event->reveal());

        $this->sheet->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->event->getId()->shouldBeCalled()->willReturn(1);
        $this->sheet->getId()->shouldBeCalled()->willReturn(123);

        $this->authorizationCheckerAdapter
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter
            ->isGranted(AgendaAccessVoter::PERMISSION, $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->authorizationCheckerAdapter
            ->isGranted(HappeningAccessVoter::PERMISSION, $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->router->generate(Route::PROGRAM, ['sheet' => 123])
            ->shouldBeCalled()
            ->willReturn('/sheet/123/program')
        ;

        $action = new AgendaOrProgramRedirectAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->router->reveal()
        );

        $result = $action($eventDomain, $this->sheet->reveal());

        $this->assertEquals('/sheet/123/program', $result->getTargetUrl());
    }

    public function testInvokeAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $eventDomain = new EventDomain($this->event->reveal());

        $this->sheet->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->event->getId()->shouldBeCalled()->willReturn(1);

        $this->authorizationCheckerAdapter
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter
            ->isGranted(AgendaAccessVoter::PERMISSION, $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->authorizationCheckerAdapter
            ->isGranted(HappeningAccessVoter::PERMISSION, $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->router->generate(Argument::any(), Argument::any())->shouldNotBeCalled();

        $action = new AgendaOrProgramRedirectAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->router->reveal()
        );

        $action($eventDomain, $this->sheet->reveal());
    }
}
