<?php

namespace Proximum\Vimeet\Tests\Application\Command\Payment;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Payment\Choice;
use Proximum\Vimeet\Application\Command\Payment\ChoiceHandler;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Cart\Converter;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Domain\Payment\TotalToPay;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ChoiceHandlerTest extends TestCase
{
    public function testHandle()
    {
        $datetime = new \DateTime();

        $event  = EventFactory::createEvent();
        $type   = new Type($event);
        $owner  = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet  = new Sheet($event, $type, [], $owner, $datetime);
        $choice = new Choice($sheet, $owner);
        $choice->mode = Mode::PAYMENT_BANK_CARD;

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

        // Expected
        $transaction = new Transaction(
            $sheet,
            480,
            $datetime,
            Mode::PAYMENT_BANK_CARD,
            null,
            Transaction::STATE_PENDING,
            'EUR',
            $owner
        );

        $order = Order::createFromSheet($sheet, $datetime);

        // Mock
        $cartManager           = $this->prophesize(CartManager::class);
        $converter             = $this->prophesize(Converter::class);
        $totalToPay            = $this->prophesize(TotalToPay::class);
        $transactionRepository = $this->prophesize(TransactionRepositoryInterface::class);
        $eventDispatcher       = $this->prophesize(DelayedEventDispatcher::class);
        $cartManager->getCart($sheet)->shouldBeCalled()->willReturn($cart);
        $converter->toOrder($cart)->shouldBeCalled()->willReturn($order);
        $totalToPay->getTotal($sheet)->shouldBeCalled()->willReturn(480);
        $transactionRepository->add($transaction)->shouldBeCalled();

        // Handler
        $handler = new ChoiceHandler(
            $transactionRepository->reveal(),
            $converter->reveal(),
            $cartManager->reveal(),
            $totalToPay->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $handler->handle($choice);
    }
}
