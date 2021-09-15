<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy\Transaction;

use Proximum\Vimeet\Behat\Service\Manager\Transaction\TransactionManager;
use Proximum\Vimeet\Behat\Service\Storage\Storage;

interface TransactionContextProxyInterface
{
    public function getStorage(): Storage;
    public function getTransactionManager(): TransactionManager;
}
