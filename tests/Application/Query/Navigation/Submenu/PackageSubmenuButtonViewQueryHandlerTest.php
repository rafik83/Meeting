<?php

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
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
        $cartManager = $this->prophesize(CartManager::class);
        $cart = $this->prophesize(Cart::class);
        $cartManager->getCart($sheet)->willReturn($cart->reveal());
        $cart->hasProducts()->willReturn(false);
        $cart->getAbsoluteProductsQuantity()->willReturn(0);

        $handler = new PackageSubmenuButtonViewQueryHandler(
            $navigationBuilder->reveal(),
            $cartManager->reveal()
        );

        $navigationBuilder
            ->getRoute('event_package_redirect_depending_on_context', ['sheet' => null])
            ->shouldBeCalled()
            ->willReturn('event_package.link');

        $packageSubmenuButtonView = $handler->handle(new PackageSubmenuButtonViewQuery($sheet, $route, 'fr', null));

        $expectedPackageSubmenuButtonView = new SubmenuButtonView(
            Category::PACKAGE_ICON,
            'navigation.category.package',
            'event_package.link',
            false,
            null
        );

        $this->assertEquals($expectedPackageSubmenuButtonView, $packageSubmenuButtonView);
    }

    public function testHandleWithStaticFormulation()
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

        $staticFormulation = $this->prophesize(StaticFormulation::class);
        $staticFormulation->getTitle('fr')->shouldBeCalled()->willReturn('My Package');

        $package = new Package($event, 'Package', $datetime);
        $package->enable(true, true, true);
        $type->setPackage($package);

        $route = 'event_package_redirect_depending_on_context';

        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);
        $cartManager = $this->prophesize(CartManager::class);
        $cart = $this->prophesize(Cart::class);
        $cartManager->getCart($sheet)->willReturn($cart->reveal());
        $cart->hasProducts()->willReturn(false);
        $cart->getAbsoluteProductsQuantity()->willReturn(0);

        $handler = new PackageSubmenuButtonViewQueryHandler(
            $navigationBuilder->reveal(),
            $cartManager->reveal()
        );

        $navigationBuilder
            ->getRoute('event_package_redirect_depending_on_context', ['sheet' => null])
            ->shouldBeCalled()
            ->willReturn('event_package.link');

        $packageSubmenuButtonView = $handler->handle(new PackageSubmenuButtonViewQuery(
            $sheet,
            $route,
            'fr',
            $staticFormulation->reveal()
        ));

        $expectedPackageSubmenuButtonView = new SubmenuButtonView(
            Category::PACKAGE_ICON,
            'My Package',
            'event_package.link',
            false,
            null
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
        $cartManager = $this->prophesize(CartManager::class);
        $cart = $this->prophesize(Cart::class);
        $cartManager->getCart($sheet)->willReturn($cart->reveal());
        $cart->hasProducts()->willReturn(true);
        $cart->getAbsoluteProductsQuantity()->willReturn(4);

        $handler = new PackageSubmenuButtonViewQueryHandler(
            $navigationBuilder->reveal(),
            $cartManager->reveal()
        );

        $navigationBuilder
            ->getRoute('event_package_redirect_depending_on_context', ['sheet' => null])
            ->shouldBeCalled()
            ->willReturn('event_package.link');

        $packageSubmenuButtonView = $handler->handle(new PackageSubmenuButtonViewQuery($sheet, $route, 'fr', null));

        $expectedPackageSubmenuButtonView = new SubmenuButtonView(
            Category::PACKAGE_ICON,
            'navigation.category.inCart',
            'event_package.link',
            true,
            4
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
        $cartManager = $this->prophesize(CartManager::class);

        $handler = new PackageSubmenuButtonViewQueryHandler($navigationBuilder->reveal(), $cartManager->reveal());
        $packageSubmenuButtonView = $handler->handle(new PackageSubmenuButtonViewQuery($sheet, 'whatever_route', 'fr', null));

        $this->assertNull($packageSubmenuButtonView);
    }
}
