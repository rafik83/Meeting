<?php

namespace Proximum\Vimeet\Behat\Proxy\Template;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\Template\SheetTemplateContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\SheetTemplateManager;

class SheetTemplateContextProxy implements SheetTemplateContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var SheetTemplateManager */
    private $sheetTemplateManager;

    public function __construct(StorageInterface $storage, SheetTemplateManager $sheetTemplateManager)
    {
        $this->storage = $storage;
        $this->sheetTemplateManager = $sheetTemplateManager;
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function getSheetTemplateManager(): SheetTemplateManager
    {
        return $this->sheetTemplateManager;
    }
}
