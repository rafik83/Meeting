<?php

namespace Proximum\Vimeet\Tests\Application\Query\Package\Summary;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Query\Package\Summary\PlanGroupViewQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\PlanGroupViewQueryHandler;
use Proximum\Vimeet\Application\Query\Package\Summary\ProductViewQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\ProductViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\Summary\PlanGroupView;
use Proximum\Vimeet\Application\View\Package\Summary\ProductView;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ProductFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class PlanGroupViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $package  = new Package($event, 'Package1', $dateTime);
        $package->enable(true, false, false);
        $product = ProductFactory::create($event, 'plan');

        $package->setPlans([$product]);
        $type->setPackage($package);

        $sheet   = SheetFactory::create($event, null, null, $type);
        $cartRow = new CartRow($sheet, $product, 1);
        $cart    = new Cart($sheet, [$cartRow], []);
        $locale  = 'fr';

        $productView = new ProductView(
            1,
            'Product1',
            25,
            1, // quantity
            25, // total
            $event->getMode(),
            20,
            $event->getCurrency()
        );

        // Mock
        $productViewQueryHandler = $this->prophesize(ProductViewQueryHandler::class);

        $productViewQueryHandler->handle(Argument::type(ProductViewQuery::class))->shouldBeCalled()->willReturn($productView);

        // Expected
        $expectedPlanGroupView = new PlanGroupView(
            '',
            [$productView],
            $productView->total
        );

        $query         = new PlanGroupViewQuery($sheet, $cart, $locale);
        $handler       = new PlanGroupViewQueryHandler($productViewQueryHandler->reveal());
        $planGroupView = $handler->handle($query);

        $this->assertEquals($planGroupView, $expectedPlanGroupView);
    }

    public function testNoPlanException()
    {
        $this->expectException(\Exception::class);

        $dateTime = new \DateTime();
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $package  = new Package($event, 'Package1', $dateTime);
        $type->setPackage($package);
        $sheet  = SheetFactory::create($event, null, null, $type);
        $cart   = new Cart($sheet, [], []);
        $locale = 'fr';

        // Mock
        $productViewQueryHandler = $this->prophesize(ProductViewQueryHandler::class);

        $productViewQueryHandler->handle(Argument::type(ProductViewQuery::class))->shouldNotBeCalled();

        $query   = new PlanGroupViewQuery($sheet, $cart, $locale);
        $handler = new PlanGroupViewQueryHandler($productViewQueryHandler->reveal());
        $handler->handle($query);
    }
}
