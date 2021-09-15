<?php

namespace Proximum\Vimeet\Application\Command\Payment;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Exception\Payment\DepositNotAvailableException;
use Proximum\Vimeet\Application\Query\Payment\PaymentConditionsViewQuery;
use Proximum\Vimeet\Domain\Cart;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;
use Proximum\Vimeet\Domain\Payment\DepositApplicable;
use Proximum\Vimeet\Domain\Payment\TotalToPay;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class ChoiceWithDepositHandler extends AbstractChoiceHandler
{
    /** @var QueryBusInterface */
    private $queryBus;

    /**
     * @param TransactionRepositoryInterface $transactionRepository
     * @param Cart\Converter                 $converter
     * @param Cart\CartManager               $cartManager
     * @param TotalToPay                     $totalToPay
     * @param DelayedEventDispatcher         $eventDispatcher
     * @param \DateTimeInterface             $dateTime
     * @param QueryBusInterface              $queryBus
     */
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        Cart\Converter $converter,
        Cart\CartManager $cartManager,
        TotalToPay $totalToPay,
        DelayedEventDispatcher $eventDispatcher,
        \DateTimeInterface $dateTime,
        QueryBusInterface $queryBus
    ) {
        parent::__construct($transactionRepository, $converter, $cartManager, $totalToPay, $eventDispatcher, $dateTime);

        $this->queryBus = $queryBus;
    }

    /**
     * @param ChoiceWithDeposit $choice
     *
     * @throws DepositNotAvailableException
     * @throws MissingBillingInfoException
     *
     * @return Transaction
     */
    public function handle(ChoiceWithDeposit $choice)
    {
        $total = $this->totalToPay->getTotal($choice->sheet);

        if ($choice->deposit) {
            $paymentConditionsView = $this->queryBus->handle(new PaymentConditionsViewQuery($choice->sheet));
            $totalDeposit = DepositApplicable::calculateDeposit($paymentConditionsView, $this->dateTime, $total);

            if ($total === $totalDeposit) {
                throw new DepositNotAvailableException('The deposit is equal to the total');
            }

            $total = $totalDeposit;
        }

        return $this->handleChoice($choice, $total);
    }
}
