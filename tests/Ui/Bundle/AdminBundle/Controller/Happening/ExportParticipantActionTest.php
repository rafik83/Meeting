<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Happening;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Happening\Export\ScheduleExport;
use Proximum\Vimeet\Application\Exception\Happening\EmptyHappeningParticipationException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening\ExportParticipantAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExportParticipantActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $request;

    /** @var ObjectProphecy|CommandBusInterface */
    private $commandBus;

    /** @var ObjectProphecy|Admin */
    private $admin;

    /** @var AdminDomain */
    private $adminDomain;

    /** @var ExportParticipantAction */
    private $exportParticipantAction;

    public function setUp()
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->event = $this->prophesize(Event::class);
        $this->request = $this->prophesize(Request::class);
        $this->admin = $this->prophesize(Admin::class);
        $this->adminDomain = new AdminDomain($this->admin->reveal());

        $this->exportParticipantAction = new ExportParticipantAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->commandBus->reveal()
        );
    }

    public function testAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        ($this->exportParticipantAction)($this->request->reveal(), $this->event->reveal(), $this->adminDomain);
    }

    public function testEmptyHappeningParticipationException()
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->request->getLocale()->willReturn('de');
        $this->event->getAvailableLocale('de')->willReturn('fr');
        $this->event->getId()->willReturn(12);

        $this->commandBus
            ->handle(new ScheduleExport($this->event->reveal(), $this->admin->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willThrow(new EmptyHappeningParticipationException())
        ;

        $this->flashBag->add('error', 'flash.admin.happening.participation.empty')->shouldBeCalled();

        $this->router->generate('admin_happening_list', ['event' => 12])->shouldBeCalled()->willReturn('/route');

        $result = ($this->exportParticipantAction)(
            $this->request->reveal(),
            $this->event->reveal(),
            $this->adminDomain
        );
        $this->assertEquals('/route', $result->getTargetUrl());
    }

    public function testExport()
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->request->getLocale()->willReturn('de');
        $this->event->getAvailableLocale('de')->willReturn('fr');
        $this->event->getId()->willReturn(12);

        $this->commandBus
            ->handle(new ScheduleExport($this->event->reveal(), $this->admin->reveal(), 'fr'))
            ->shouldBeCalled()
        ;

        $this->flashBag->add('success', 'flash.admin.happening.participation.export_scheduled')->shouldBeCalled();

        $this->router->generate('admin_happening_list', ['event' => 12])->shouldBeCalled()->willReturn('/route');

        $result = ($this->exportParticipantAction)(
            $this->request->reveal(),
            $this->event->reveal(),
            $this->adminDomain
        );
        $this->assertEquals('/route', $result->getTargetUrl());
    }
}
