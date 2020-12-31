<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\BillingInfoManager;

interface BillingInfoProxyInterface
{
    public function getStorage(): StorageInterface;

    public function getBillingInfoManager(): BillingInfoManager;
}
