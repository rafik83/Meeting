<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Spot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot\ScheduleExport;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot\ScheduleExportHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Spot\ScheduleExportAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ScheduleExportActionTest extends TestCase
{
    public function testInvoke()
    {
        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(1337);

        $admin = $this->prophesize(Admin::class);
        $adminDomain = $this->prophesize(AdminDomain::class);
        $adminDomain->getAdmin()->willReturn($admin->reveal());

        $authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->willReturn(true);
        $authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event->reveal())->willReturn(true);

        $flashBag = $this->prophesize(FlashBagInterface::class);
        $flashBag->add('success', 'flash.spot.export.scheduled')->shouldBeCalled();

        $router = $this->prophesize(RouterInterface::class);
        $router->generate('admin_spot_list', ['event' => 1337])->shouldBeCalled()->willReturn('/event/1337/export');

        $scheduleExportHandler = $this->prophesize(ScheduleExportHandler::class);
        $scheduleExportHandler->handle(new ScheduleExport($event->reveal(), $admin->reveal()))->shouldBeCalled();

        $scheduleExportAction = new ScheduleExportAction(
            $authorizationCheckerAdapter->reveal(),
            $flashBag->reveal(),
            $router->reveal(),
            $scheduleExportHandler->reveal()
        );
        $result = $scheduleExportAction($event->reveal(), $adminDomain->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    public function testAccessDeniedException()
    {
        $this->expectException(AccessDeniedException::class);

        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(1337);

        $admin = $this->prophesize(Admin::class);
        $adminDomain = $this->prophesize(AdminDomain::class);
        $adminDomain->getAdmin()->willReturn($admin->reveal());

        $authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->willReturn(true);
        $authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event->reveal())->willReturn(false);

        $flashBag = $this->prophesize(FlashBagInterface::class);
        $router = $this->prophesize(RouterInterface::class);
        $scheduleExportHandler = $this->prophesize(ScheduleExportHandler::class);

        $scheduleExportAction = new ScheduleExportAction(
            $authorizationCheckerAdapter->reveal(),
            $flashBag->reveal(),
            $router->reveal(),
            $scheduleExportHandler->reveal()
        );
        $scheduleExportAction($event->reveal(), $adminDomain->reveal());
    }
}
