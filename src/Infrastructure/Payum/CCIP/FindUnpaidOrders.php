<?php

namespace Proximum\Vimeet\Infrastructure\Payum\CCIP;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;

class FindUnpaidOrders
{
    private TransactionRepositoryInterface $transactionRepository;

    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Get all order views that are not referenced in a paid transaction
     * @param int[] $orderIds
     * @return int[]
     */
    public function fromOrderIds(Sheet $sheet, array $orderIds): array
    {
        $paidTransactions = $this->transactionRepository->findPaid($sheet);

        $paidOrderIds = array_reduce(
            $paidTransactions,
            fn ($carry, Transaction $transaction) => [...$carry, ...explode(',', $transaction->getInternalReference())],
            []
        );

        return array_values(array_filter($orderIds, function (int $orderIds) use ($paidOrderIds) {
            return !in_array($orderIds, $paidOrderIds);
        }));
    }
}
