<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy\Sheet;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Sheet\SheetCompletenessManager;

interface SheetCompletenessContextProxyInterface
{
    public function getStorage(): StorageInterface;
    public function getSheetCompletenessManager(): SheetCompletenessManager;
}
