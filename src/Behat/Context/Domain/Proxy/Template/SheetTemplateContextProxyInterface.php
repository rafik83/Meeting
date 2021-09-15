<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy\Template;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\SheetTemplateManager;

interface SheetTemplateContextProxyInterface
{
    public function getStorage(): StorageInterface;

    public function getSheetTemplateManager(): SheetTemplateManager;
}
