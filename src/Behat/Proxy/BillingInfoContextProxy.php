<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\BillingInfoProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\BillingInfoManager;

class BillingInfoContextProxy implements BillingInfoProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var BillingInfoManager */
    private $billingInfoManager;

    /**
     * CatalogVisibilityContextProxy constructor.
     *
     * @param StorageInterface   $storage
     * @param BillingInfoManager $billingInfoManager
     */
    public function __construct(
        StorageInterface $storage,
        BillingInfoManager $billingInfoManager
    ) {
        $this->storage = $storage;
        $this->billingInfoManager = $billingInfoManager;
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function getBillingInfoManager(): BillingInfoManager
    {
        return $this->billingInfoManager;
    }
}
