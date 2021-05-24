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
     * Get all order ids that are not referenced in a CCIP paid transaction
     * @return int[] order ids
     */
    public function findBySheet(Sheet $sheet): array
    {
        $sheetTransactions = $this->transactionRepository->findBySheet($sheet);

        $unpaidOrderIds = array_reduce(
            $sheetTransactions,
            function ($carry, Transaction $transaction) {
                if ($transaction->isCCIP() && !$transaction->isPaid()) {
                    return [...$carry, ...explode(',', $transaction->getInternalReference())];
                }

                return $carry;
            },
            []
        );

        return array_values($unpaidOrderIds);
    }
}
