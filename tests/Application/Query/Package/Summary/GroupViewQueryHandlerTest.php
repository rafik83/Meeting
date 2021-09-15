<?php

namespace Proximum\Vimeet\Tests\Application\Query\Package\Summary;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Query\Package\Summary\GroupViewQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\GroupViewQueryHandler;
use Proximum\Vimeet\Application\Query\Package\Summary\ProductViewQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\ProductViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\Summary\GroupView;
use Proximum\Vimeet\Application\View\Package\Summary\PlanGroupView;
use Proximum\Vimeet\Application\View\Package\Summary\ProductView;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\PackageGroup;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ProductFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class GroupViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $locale   = 'fr';
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $package  = new Package($event, 'Package1', $dateTime);
        $package->enable(false, false, true);
        $sheet   = SheetFactory::create($event, null, $dateTime, $type);
        $product = ProductFactory::create($event, 'option');

        $package->setPlanning($product);
        $type->setPackage($package);

        $cartRow = new CartRow($sheet, $product, 1);
        $cart    = new Cart($sheet, [$cartRow], []);
        $group   = new PackageGroup($package, 1);
        $group->setOptions([$product]);

        $planGroupView = new PlanGroupView('label', [], 0.0);

        $productView = new ProductView(
            1,
            'Option1',
            25,
            1, // quantity
            25, // total
            $event->getMode(),
            20,
            $event->getCurrency()
        );

        // Expected
        $expectedGroupView = new GroupView('', [$productView], 25);

        // Mock
        $productViewQueryHandler = $this->prophesize(ProductViewQueryHandler::class);

        $productViewQueryHandler->handle(Argument::type(ProductViewQuery::class))->shouldBeCalled()->willReturn($productView);

        $handler           = new GroupViewQueryHandler($productViewQueryHandler->reveal());
        $query             = new GroupViewQuery($sheet, $group, $cart, $locale, $planGroupView);
        $planningGroupView = $handler->handle($query);

        $this->assertEquals($planningGroupView, $expectedGroupView);
    }
}
