<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\BillingInfoProxyInterface;

class BillingInfoContext implements Context
{
    /** @var BillingInfoProxyInterface */
    public $billingInfoProxy;

    public function __construct(BillingInfoProxyInterface $billingInfoProxy)
    {
        $this->billingInfoProxy = $billingInfoProxy;
    }

    /**
     * @Given /^there is billing info for this sheet$/
     */
    public function thereIsBillingInfoForThisSheet(): void
    {
        $sheet = $this->billingInfoProxy->getStorage()->get('sheet');

        if (null === $sheet) {
            throw new \InvalidArgumentException('Sheet missing');
        }

        $billingInfo = $this->billingInfoProxy->getBillingInfoManager()->create($sheet, 'FR');

        $this->billingInfoProxy->getStorage()->set('billingInfo', $billingInfo);
    }
}
