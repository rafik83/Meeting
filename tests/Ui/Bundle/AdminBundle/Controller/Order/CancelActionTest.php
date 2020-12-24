<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Order;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Order\CancelAll;
use Proximum\Vimeet\Domain\Exception\Order\OrderCanNotBeCancelledException;
use Proximum\Vimeet\Domain\Intention\IntentionType;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Order\CancelAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CancelActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter, $router, $flashBag, $csrfTokenManager, $commandBus, $event, $sheet, $admin, $request;

    public function setUp()
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->csrfTokenManager = $this->prophesize(CsrfTokenManagerInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->event = $this->prophesize(Event::class);
        $this->admin = $this->prophesize(Admin::class);
        $this->request = $this->prophesize(Request::class);
    }

    public function testInvokeNoCorrectRole(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->commandBus->handle(new CancelAll($this->sheet->reveal(), $this->admin->reveal()))->shouldNotBeCalled();

        $this->authorizationCheckerAdapter
            ->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new CancelAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->csrfTokenManager->reveal(),
            $this->commandBus->reveal()
        );

        $action(
            $this->request->reveal(),
            $this->event->reveal(),
            $this->sheet->reveal(),
            new AdminDomain($this->admin->reveal())
        );
    }

    public function testInvokeNoAccessToEvent(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->commandBus->handle(new CancelAll($this->sheet->reveal(), $this->admin->reveal()))->shouldNotBeCalled();

        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->authorizationCheckerAdapter
            ->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $action = new CancelAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->csrfTokenManager->reveal(),
            $this->commandBus->reveal()
        );

        $action(
            $this->request->reveal(),
            $this->event->reveal(),
            $this->sheet->reveal(),
            new AdminDomain($this->admin->reveal())
        );
    }

    public function testInvokeSheetNotOnEvent(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->commandBus->handle(new CancelAll($this->sheet->reveal(), $this->admin->reveal()))->shouldNotBeCalled();

        $event = $this->prophesize(Event::class);
        $this->sheet->getEvent()->willReturn($event->reveal());
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter
            ->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $action = new CancelAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->csrfTokenManager->reveal(),
            $this->commandBus->reveal()
        );

        $action(
            $this->request->reveal(),
            $this->event->reveal(),
            $this->sheet->reveal(),
            new AdminDomain($this->admin->reveal())
        );
    }

    public function testInvokeTokenNotValid(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->commandBus->handle(new CancelAll($this->sheet->reveal(), $this->admin->reveal()))->shouldNotBeCalled();

        $this->request->get('_token')->shouldBeCalled()->willReturn('toto');

        $this->csrfTokenManager
            ->isTokenValid(new CsrfToken(IntentionType::INTENTION_CANCEL_ORDERS, 'toto'))
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->sheet->getEvent()->willReturn($this->event->reveal());
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter
            ->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $action = new CancelAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->csrfTokenManager->reveal(),
            $this->commandBus->reveal()
        );

        $action(
            $this->request->reveal(),
            $this->event->reveal(),
            $this->sheet->reveal(),
            new AdminDomain($this->admin->reveal())
        );
    }

    public function testInvokeOrderInvoiced(): void
    {
        $this->event->getId()->willReturn(12);
        $this->sheet->getId()->willReturn(13);

        $this->router->generate('admin_sheet_details', [
            'event' => 12,
            'sheet' => 13,
            '_fragment' => 'sheetOrders',
        ])->shouldBeCalled()->willReturn('path/to/route');

        $this->commandBus
            ->handle(new CancelAll($this->sheet->reveal(), $this->admin->reveal()))
            ->shouldBeCalled()
            ->willThrow(OrderCanNotBeCancelledException::class)
        ;

        $this->flashBag->add('error', 'flash.admin.orders.cancelled.error')->shouldBeCalled();

        $this->csrfTokenManager
            ->refreshToken(IntentionType::INTENTION_CANCEL_ORDERS)
            ->shouldBeCalled()
        ;
        $this->request->get('_token')->shouldBeCalled()->willReturn('toto');
        $this->csrfTokenManager
            ->isTokenValid(new CsrfToken(IntentionType::INTENTION_CANCEL_ORDERS, 'toto'))
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->sheet->getEvent()->willReturn($this->event->reveal());
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter
            ->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $action = new CancelAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->csrfTokenManager->reveal(),
            $this->commandBus->reveal()
        );

        $action(
            $this->request->reveal(),
            $this->event->reveal(),
            $this->sheet->reveal(),
            new AdminDomain($this->admin->reveal())
        );
    }

    public function testInvoke(): void
    {
        $this->event->getId()->willReturn(12);
        $this->sheet->getId()->willReturn(13);

        $this->router->generate('admin_sheet_details', [
            'event' => 12,
            'sheet' => 13,
            '_fragment' => 'sheetOrders',
        ])->shouldBeCalled()->willReturn('path/to/route');

        $this->commandBus
            ->handle(new CancelAll($this->sheet->reveal(), $this->admin->reveal()))
            ->shouldBeCalled()
        ;

        $this->flashBag->add('success', 'flash.admin.orders.cancelled.success')->shouldBeCalled();

        $this->csrfTokenManager
            ->refreshToken(IntentionType::INTENTION_CANCEL_ORDERS)
            ->shouldBeCalled()
        ;
        $this->request->get('_token')->shouldBeCalled()->willReturn('toto');
        $this->csrfTokenManager
            ->isTokenValid(new CsrfToken(IntentionType::INTENTION_CANCEL_ORDERS, 'toto'))
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->sheet->getEvent()->willReturn($this->event->reveal());
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter
            ->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $action = new CancelAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->csrfTokenManager->reveal(),
            $this->commandBus->reveal()
        );

        $action(
            $this->request->reveal(),
            $this->event->reveal(),
            $this->sheet->reveal(),
            new AdminDomain($this->admin->reveal())
        );
    }
}
