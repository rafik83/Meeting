<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class PackageSubmenuButtonViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
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

        $route = 'event_package';

        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);
        $cartRowRepository = $this->prophesize(CartRowRepositoryInterface::class);

        $handler = new PackageSubmenuButtonViewQueryHandler($navigationBuilder->reveal(), $cartRowRepository->reveal());

        $navigationBuilder
            ->getRoute('event_package')
            ->shouldBeCalled()
            ->willReturn('event_package.link');

        $packageSubmenuButtonView = $handler->handle(new PackageSubmenuButtonViewQuery($sheet, $route));

        $expectedPackageSubmenuButtonView = new SubmenuButtonView(
            Category::PACKAGE_ICON,
            'package.title',
            'event_package.link',
            true,
            false
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
