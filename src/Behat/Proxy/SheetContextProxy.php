<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\SheetContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\SheetManager;

class SheetContextProxy implements SheetContextProxyInterface
{
    /** @var SheetManager */
    private $sheetManager;

    /** @var StorageInterface */
    private $storage;

    /**
     * @param StorageInterface $storage
     * @param SheetManager     $sheetManager
     */
    public function __construct(StorageInterface $storage, SheetManager $sheetManager)
    {
        $this->sheetManager = $sheetManager;
        $this->storage      = $storage;
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
}
