<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Context\Domain\Invoice;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\Invoice\PrefixContextProxyInterface;

class PrefixContext implements Context
{
    /** @var PrefixContextProxyInterface */
    private $prefixContextProxy;

    /**
     * @param PrefixContextProxyInterface $prefixContextProxy
     */
    public function __construct(PrefixContextProxyInterface $prefixContextProxy)
    {
        $this->prefixContextProxy = $prefixContextProxy;
    }

    /**
     * @Given /^the invoice prefix with name "(?P<prefixName>[^"]+)" and prefix "(?P<prefix>[^"]+)" is created$/
     *
     * @param string $prefixName
     * @param string $prefix
     */
    public function createPrefix(string $prefixName, string $prefix)
    {
        $invoicePrefix = $this->prefixContextProxy->getPrefixManager()->create($prefixName, $prefix);
        $this->prefixContextProxy->getStorage()->set('invoicePrefix', $invoicePrefix);
    }

    /**
     * @Given /^the invoice prefix with name "(?P<prefixName>[^"]+)" and prefix "(?P<prefix>[^"]+)" is created and is default$/
     *
     * @param string $prefixName
     * @param string $prefix
     */
    public function createDefaultPrefix(string $prefixName, string $prefix)
    {
        $invoicePrefix = $this->prefixContextProxy->getPrefixManager()->create($prefixName, $prefix, true);
        $this->prefixContextProxy->getStorage()->set('invoicePrefix', $invoicePrefix);
    }
}
