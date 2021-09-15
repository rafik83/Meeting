<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\InvoiceContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\InvoiceManager;
use Proximum\Vimeet\Behat\Service\Manager\SheetManager;

class InvoiceContextProxy implements InvoiceContextProxyInterface
{
    /** @var SheetManager */
    private $sheetManager;

    /** @var StorageInterface */
    private $storage;

    /** @var InvoiceManager */
    private $invoiceManager;

    /**
     * @param StorageInterface $storage
     * @param SheetManager     $sheetManager
     * @param InvoiceManager   $invoiceManager
     */
    public function __construct(StorageInterface $storage, SheetManager $sheetManager, InvoiceManager $invoiceManager)
    {
        $this->sheetManager   = $sheetManager;
        $this->storage        = $storage;
        $this->invoiceManager = $invoiceManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return SheetManager
     */
    public function getSheetManager()
    {
        return $this->sheetManager;
    }

    /**
     * @return InvoiceManager
     */
    public function getInvoiceManager()
    {
        return $this->invoiceManager;
    }
}
