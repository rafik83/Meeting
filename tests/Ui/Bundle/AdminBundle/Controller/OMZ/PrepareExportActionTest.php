<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\OMZ;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\OMZ\ScheduleExport;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\OMZ\PrepareExportAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PrepareExportActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $admin;

    /** @var AdminDomain */
    private $adminDomain;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->admin = $this->prophesize(Admin::class);
        $this->adminDomain = new AdminDomain($this->admin->reveal());
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
    }

    public function testAuthorizationOrganizer()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->flashBag->add(Argument::any())->shouldNotBeCalled();
        $this->router->generate(Argument::any())->shouldNotBeCalled();

        $action = new PrepareExportAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal()
        );

        $action($this->event->reveal(), $this->adminDomain);
    }

    public function testAuthorizationEvent()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->flashBag->add(Argument::any())->shouldNotBeCalled();
        $this->router->generate(Argument::any())->shouldNotBeCalled();

        $action = new PrepareExportAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal()
        );

        $action($this->event->reveal(), $this->adminDomain);
    }

    public function testInvoke()
    {
        $this->event->getId()->willReturn(12);

        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->commandBus->handle(new ScheduleExport($this->event->reveal(), $this->admin->reveal()))->shouldBeCalled();
        $this->flashBag->add('success', 'flash.omz.export.prepare')->shouldBeCalled();
        $this->router
            ->generate('admin_agenda_export_participant_index', ['event' => 12])
            ->shouldBeCalled()
            ->willReturn('/path/to/route')
        ;

        $action = new PrepareExportAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal()
        );

        $result = $action($this->event->reveal(), $this->adminDomain);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/path/to/route', $result->getTargetUrl());
    }
}
