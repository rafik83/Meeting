<?php

namespace Proximum\Vimeet\Tests\Domain\Payment;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Package\Specification\VatApplicable;
use Proximum\Vimeet\Domain\Payment\TotalToPay;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class TotalToPayTest extends TestCase
{
    public function testGetTotal()
    {
        $dateTime = new \DateTime();
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $owner    = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $package  = new Package($event, 'title', $dateTime);
        $sheet    = new Sheet($event, $type, [], $owner, $dateTime);
        $type->setPackage($package);

        $plan = Product::createPlan($event, 'plan', '', 200, 20, 20, 100);
        $plan->translate('fr', 'plan', '', '', '', '');
        $plan->translate('en', 'plan', '', '', '', '');
        $chair = Product::createOption($event, 'chair', '', 100, 20, 2, 20, 100, true, null, null, null);
        $chair->translate('fr', 'chair', '', '', '', '');
        $chair->translate('en', 'chair', '', '', '', '');

        $planRow  = new CartRow($sheet, $plan, 1);
        $chairRow = new CartRow($sheet, $chair, 2);
        $currentStep = 4;
        $cart  = new Cart($sheet, [$planRow, $chairRow], [], $currentStep);

        // Mock
        $cartManager   = $this->prophesize(CartManager::class);
        $vatApplicable = $this->prophesize(VatApplicable::class);
        $cartManager->getCart($sheet)->shouldBeCalled()->willReturn($cart);
        $vatApplicable->onSheet($sheet)->shouldBeCalled()->willReturn(true);

        $totalToPay = new TotalToPay($cartManager->reveal(), $vatApplicable->reveal());
        $this->assertEquals(480, $totalToPay->getTotal($sheet));
    }
}
