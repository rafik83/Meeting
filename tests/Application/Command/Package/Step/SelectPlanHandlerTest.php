<?php

namespace Proximum\Vimeet\Tests\Application\Command\Package\Step;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Package\Step\SelectPlan;
use Proximum\Vimeet\Application\Command\Package\Step\SelectPlanHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Package\StepDoneEvent;
use Proximum\Vimeet\Domain\Cart\BuyableObjectResolver;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\ProductAttributedToParticipant\ProductsAttributedToParticipantRemoveAllBySheet;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SelectPlanHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $dateTime = new \DateTime();
        $user     = new User('email@email.com', 'salt', 'password', 'fr');
        $sheet    = new Sheet($event, $type, [], $user, $dateTime);
        $product  = Product::createPlan($event, 'plan', '', 100, 20, 10, 40);

        $emptyCart    = new Cart($sheet, [], [], 1);
        $expectedCart = new Cart($sheet, [new CartRow($sheet, $product, 1)], [], 1);

        // Mock
        $cartManager = $this->prophesize(CartManager::class);
        $cartManager->getCart($sheet, 1)->shouldBeCalled()->willReturn($emptyCart);
        $cartManager->deleteCartStep($emptyCart)->shouldBeCalled();
        $cartManager->save($expectedCart)->shouldBeCalled();
        $buyableObjectResolver = $this->prophesize(BuyableObjectResolver::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $packageStepDone = new StepDoneEvent($sheet, 'plan');
        $eventDispatcher->dispatch(Events::PACKAGE_STEP_DONE, $packageStepDone)->shouldBeCalled();

        $productsAttributedToParticipantRemoveAllBySheet = $this->prophesize(
            ProductsAttributedToParticipantRemoveAllBySheet::class
        );
        $productsAttributedToParticipantRemoveAllBySheet->handle($sheet)->shouldNotBeCalled();

        $plans       = new SelectPlan($sheet, 1);
        $plans->plan = $product;

        $plansHandler = new SelectPlanHandler(
            $cartManager->reveal(),
            $buyableObjectResolver->reveal(),
            $eventDispatcher->reveal(),
            $productsAttributedToParticipantRemoveAllBySheet->reveal()
        );
        $plansHandler->handle($plans);
    }

    public function testHandleWithExistingCartRow(): void
    {
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $dateTime = new \DateTime();
        $product1 = Product::createPlan($event, 'plan1', '', 100, 20, 10, 40);
        $product2 = Product::createPlan($event, 'plan2', '', 50, 20, 10, 50);
        $user     = new User('email@email.com', 'salt', 'password', 'fr');
        $sheet    = new Sheet($event, $type, [], $user, $dateTime);

        $actualCart   = new Cart($sheet, [new CartRow($sheet, $product1, 1)], [], 1);
        $expectedCart = new Cart($sheet, [new CartRow($sheet, $product2, 1)], [], 1);

        // Mock
        $cartManager           = $this->prophesize(CartManager::class);
        $buyableObjectResolver = $this->prophesize(BuyableObjectResolver::class);
        $cartManager->getCart($sheet, 1)->shouldBeCalled()->willReturn($actualCart);
        $cartManager->deleteCartStep($actualCart)->shouldBeCalled();
        $cartManager->save(Argument::that(function (Cart $cart) use ($expectedCart) {
            $this->assertEquals($expectedCart->getSheet(), $cart->getSheet());
            $this->assertEquals($expectedCart->getRows(), $cart->getRows());

            return true;
        }))->shouldBeCalled();
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $packageStepDone = new StepDoneEvent($sheet, 'plan');
        $eventDispatcher->dispatch(Events::PACKAGE_STEP_DONE, $packageStepDone)->shouldBeCalled();

        $productsAttributedToParticipantRemoveAllBySheet = $this->prophesize(
            ProductsAttributedToParticipantRemoveAllBySheet::class
        );
        $productsAttributedToParticipantRemoveAllBySheet->handle($sheet)->shouldBeCalled();

        $plans       = new SelectPlan($sheet, 1);
        $plans->plan = $product2;

        $plansHandler = new SelectPlanHandler(
            $cartManager->reveal(),
            $buyableObjectResolver->reveal(),
            $eventDispatcher->reveal(),
            $productsAttributedToParticipantRemoveAllBySheet->reveal()
        );
        $plansHandler->handle($plans);
    }
}
