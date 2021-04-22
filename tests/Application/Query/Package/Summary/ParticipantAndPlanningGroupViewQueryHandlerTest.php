<?php

namespace Proximum\Vimeet\Tests\Application\Query\Package\Summary;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Query\Package\Summary\ParticipantAndPlanningGroupViewQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\ParticipantAndPlanningGroupViewQueryHandler;
use Proximum\Vimeet\Application\Query\Package\Summary\ProductViewQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\ProductViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\Summary\ParticipantAndPlanningGroupView;
use Proximum\Vimeet\Application\View\Package\Summary\PlanGroupView;
use Proximum\Vimeet\Application\View\Package\Summary\ProductView;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ProductFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class ParticipantAndPlanningGroupViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $locale   = 'fr';
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $package  = new Package($event, 'Package1', $dateTime);
        $package->enable(false, true, false);
        $sheet   = SheetFactory::create($event, null, $dateTime, $type);
        $product = ProductFactory::create($event, 'participant');

        $package->setParticipants([$product]);
        $type->setPackage($package);

        $cartRow = new CartRow($sheet, $product, 1);
        $cart    = new Cart($sheet, [$cartRow], []);

        $planGroupView = new PlanGroupView('label', [], 0.0);

        $productView = new ProductView(
            1,
            'Participant1',
            25,
            1, // quantity
            25, // total
            $event->getMode(),
            20,
            $event->getCurrency()
        );

        // Expected
        $expectedParticipantGroupView = new ParticipantAndPlanningGroupView('', [$productView], 25);

        // Mock
        $productViewQueryHandler = $this->prophesize(ProductViewQueryHandler::class);

        $productViewQueryHandler->handle(Argument::type(ProductViewQuery::class))->shouldBeCalled()->willReturn($productView);

        $handler              = new ParticipantAndPlanningGroupViewQueryHandler($productViewQueryHandler->reveal());
        $query                = new ParticipantAndPlanningGroupViewQuery($sheet, $cart, $locale, $planGroupView);
        $participantGroupView = $handler->handle($query);

        $this->assertEquals($participantGroupView, $expectedParticipantGroupView);
    }

    public function testParticipantNotEnabledException()
    {
        $this->expectException(\Exception::class);

        $dateTime = new \DateTime();
        $locale   = 'fr';
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $package  = new Package($event, 'Package1', $dateTime);
        $package->enable(false, false, false);
        $sheet   = SheetFactory::create($event, null, $dateTime, $type);
        $product = ProductFactory::create($event, 'other_than_participant');

        $package->setParticipants([$product]);
        $type->setPackage($package);

        $cart = new Cart($sheet, [], []);

        $planGroupView = new PlanGroupView('label', [], 0.0);

        // Mock
        $productViewQueryHandler = $this->prophesize(ProductViewQueryHandler::class);

        $productViewQueryHandler->handle(Argument::type(ProductViewQuery::class))->shouldNotBeCalled();

        $handler = new ParticipantAndPlanningGroupViewQueryHandler($productViewQueryHandler->reveal());
        $query   = new ParticipantAndPlanningGroupViewQuery($sheet, $cart, $locale, $planGroupView);

        $handler->handle($query);
    }
}
