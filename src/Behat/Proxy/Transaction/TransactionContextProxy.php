<?php

namespace Proximum\Vimeet\Behat\Proxy\Transaction;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\Transaction\TransactionContextProxyInterface;
use Proximum\Vimeet\Behat\Service\Manager\Transaction\TransactionManager;
use Proximum\Vimeet\Behat\Service\Storage\Storage;

class TransactionContextProxy implements TransactionContextProxyInterface
{
    /** @var Storage */
    private $storage;

    /** @var TransactionManager */
    private $transactionManager;

    public function __construct(
        Storage $storage,
        TransactionManager $transactionManager
    ) {
        $this->storage = $storage;
        $this->transactionManager = $transactionManager;
    }

    public function getStorage(): Storage
    {
        return $this->storage;
    }

    public function getTransactionManager(): TransactionManager
    {
        return $this->transactionManager;
    }
}
