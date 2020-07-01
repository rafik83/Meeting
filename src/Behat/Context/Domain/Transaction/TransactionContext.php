<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Transaction;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\Transaction\TransactionContextProxyInterface;

class TransactionContext implements Context
{
    /** @var TransactionContextProxyInterface */
    private $transactionContextProxy;

    public function __construct(TransactionContextProxyInterface $transactionContextProxy)
    {
        $this->transactionContextProxy = $transactionContextProxy;
    }

    /**
     * @Given /^there is a pending transaction with reference "(?P<ref>[^"]+)" and amount (?P<amount>\d+) for this sheet$/
     *
     * @param string $ref
     * @param float $amount
     */
    public function createPendingTransaction(string $ref, float $amount): void
    {
        $sheet = $this->transactionContextProxy->getStorage()->get('sheet');

        if (null === $sheet) {
            throw new \InvalidArgumentException('Missing Sheet');
        }

        $transaction = $this->transactionContextProxy
            ->getTransactionManager()
            ->createPendingTransaction($sheet, $amount, $ref);

        $this->transactionContextProxy->getStorage()->set('transaction', $transaction);
    }
}
