<?php

namespace Proximum\Vimeet\Behat\Proxy\Sheet;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\Sheet\SheetCompletenessContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Sheet\SheetCompletenessManager;

class SheetCompletenessContextProxy implements SheetCompletenessContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var SheetCompletenessManager */
    private $sheetCompletenessManager;

    public function __construct(StorageInterface $storage, SheetCompletenessManager $sheetCompletenessManager)
    {
        $this->storage = $storage;
        $this->sheetCompletenessManager = $sheetCompletenessManager;
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function getSheetCompletenessManager(): SheetCompletenessManager
    {
        return $this->sheetCompletenessManager;
    }
}
