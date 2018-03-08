<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class PackageSubmenuButtonViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $datetime = new \DateTime();
        $event    = EventFactory::createEvent();
        $type = new Type($event);

        $sheet = new Sheet(
            $event,
            $type,
            [],
            new User('email@email.com', 'salt', 'password', 'fr'),
            $datetime
        );

        $package = new Package($event, 'Package', $datetime);
        $package->enable(true, true, true);
        $type->setPackage($package);

        $route = 'event_package_redirect_depending_on_context';

        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);
        $cartRowRepository = $this->prophesize(CartRowRepositoryInterface::class);

        $handler = new PackageSubmenuButtonViewQueryHandler(
            $navigationBuilder->reveal(),
            $cartRowRepository->reveal()
        );

        $navigationBuilder
            ->getRoute('event_package_redirect_depending_on_context', ['sheet' => null])
            ->shouldBeCalled()
            ->willReturn('event_package.link');
        $cartRowRepository->hasProducts($sheet)->shouldBeCalled()->willReturn(false);

        $packageSubmenuButtonView = $handler->handle(new PackageSubmenuButtonViewQuery($sheet, $route));

        $expectedPackageSubmenuButtonView = new SubmenuButtonView(
            Category::PACKAGE_ICON,
            'navigation.category.package',
            'event_package.link',
            false,
            false
        );

        $this->assertEquals($expectedPackageSubmenuButtonView, $packageSubmenuButtonView);
    }

    public function testHandleWithProductInCart()
    {
        $datetime = new \DateTime();
        $event    = EventFactory::createEvent();
        $type = new Type($event);

        $sheet = new Sheet(
            $event,
            $type,
            [],
            new User('email@email.com', 'salt', 'password', 'fr'),
            $datetime
        );
        $order       = new Order($sheet, '', new \DateTime());
        $sheet->addOrder($order);

        $package = new Package($event, 'Package', $datetime);
        $package->enable(true, true, true);
        $type->setPackage($package);

        $route = 'event_order_summary_total';

        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);
        $cartRowRepository = $this->prophesize(CartRowRepositoryInterface::class);

        $handler = new PackageSubmenuButtonViewQueryHandler(
            $navigationBuilder->reveal(),
            $cartRowRepository->reveal()
        );

        $navigationBuilder
            ->getRoute('event_package_redirect_depending_on_context', ['sheet' => null])
            ->shouldBeCalled()
            ->willReturn('event_package.link');
        $cartRowRepository->hasProducts($sheet)->shouldBeCalled()->willReturn(true);

        $packageSubmenuButtonView = $handler->handle(new PackageSubmenuButtonViewQuery($sheet, $route));

        $expectedPackageSubmenuButtonView = new SubmenuButtonView(
            Category::PACKAGE_ICON,
            'navigation.category.package',
            'event_package.link',
            true,
            true
        );

        $this->assertEquals($expectedPackageSubmenuButtonView, $packageSubmenuButtonView);
    }

    public function testHandleNoEnabledPackage()
    {
        $datetime = new \DateTime();
        $event    = EventFactory::createEvent();
        $type = new Type($event);

        $sheet = new Sheet(
            $event,
            $type,
            [],
            new User('email@email.com', 'salt', 'password', 'fr'),
            $datetime
        );

        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);
        $cartRowRepository = $this->prophesize(CartRowRepositoryInterface::class);

        $handler = new PackageSubmenuButtonViewQueryHandler($navigationBuilder->reveal(), $cartRowRepository->reveal());
        $packageSubmenuButtonView = $handler->handle(new PackageSubmenuButtonViewQuery($sheet, 'whatever_route'));

        $this->assertNull($packageSubmenuButtonView);
    }
}
