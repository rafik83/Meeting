<?php

namespace Proximum\Vimeet\Tests\Application\Command\Payment;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Payment\ChoiceWithDeposit;
use Proximum\Vimeet\Application\Command\Payment\ChoiceWithDepositHandler;
use Proximum\Vimeet\Application\Query\Payment\PaymentConditionsViewQuery;
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
use Proximum\Vimeet\Domain\Payment\PaymentConditionsView;
use Proximum\Vimeet\Domain\Payment\TotalToPay;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ChoiceWithDepositHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime('2017-10-10 10:10:00');

        $event  = EventFactory::createEvent();
        $type   = new Type($event);
        $owner  = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet  = new Sheet($event, $type, [], $owner, $dateTime);
        $choice = new ChoiceWithDeposit($sheet, $owner);
        $choice->mode    = Mode::PAYMENT_BANK_CARD;
        $choice->deposit = true;

        $queryBus = $this->prophesize(QueryBusInterface::class);
        $paymentConditionsView = new PaymentConditionsView(
            [Mode::PAYMENT_BANK_CARD],
            true,
            new \DateTime('2020-10-10 10:10:10'),
            200,
            50
        );
        $queryBus->handle(new PaymentConditionsViewQuery($sheet))->shouldBeCalled()->willReturn($paymentConditionsView);

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
            240,
            $dateTime,
            Mode::PAYMENT_BANK_CARD,
            null,
            Transaction::STATE_PENDING,
            'EUR',
            $owner
        );

        $order = Order::createFromSheet($sheet, $dateTime);

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
        $handler = new ChoiceWithDepositHandler(
            $transactionRepository->reveal(),
            $converter->reveal(),
            $cartManager->reveal(),
            $totalToPay->reveal(),
            $eventDispatcher->reveal(),
            $dateTime,
            $queryBus->reveal()
        );

        $handler->handle($choice);
    }

    public function testHandleWithoutDeposit()
    {
        $dateTime = new \DateTime('2017-10-10 10:10:10');

        $event  = EventFactory::createEvent();
        $type   = new Type($event);
        $owner  = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet  = new Sheet($event, $type, [], $owner, $dateTime);
        $choice = new ChoiceWithDeposit($sheet, $owner);
        $choice->mode    = Mode::PAYMENT_BANK_CARD;
        $choice->deposit = false;

        $queryBus = $this->prophesize(QueryBusInterface::class);
        $queryBus->handle(new PaymentConditionsViewQuery($sheet))->shouldNotBeCalled()->willReturn();

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
            $dateTime,
            Mode::PAYMENT_BANK_CARD,
            null,
            Transaction::STATE_PENDING,
            'EUR',
            $owner
        );

        $order = Order::createFromSheet($sheet, $dateTime);

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
        $handler = new ChoiceWithDepositHandler(
            $transactionRepository->reveal(),
            $converter->reveal(),
            $cartManager->reveal(),
            $totalToPay->reveal(),
            $eventDispatcher->reveal(),
            $dateTime,
            $queryBus->reveal()
        );

        $handler->handle($choice);
    }
}
