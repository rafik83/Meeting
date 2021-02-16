<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\InvoiceManager;
use Proximum\Vimeet\Behat\Service\Manager\SheetManager;

interface InvoiceContextProxyInterface
{
    /**
     * @return StorageInterface
     */
    public function getStorage();

    /**
     * @return SheetManager
     */
    public function getSheetManager();

    /**
     * @return InvoiceManager
     */
    public function getInvoiceManager();
}
