<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\NomenclatureManager;

interface NomenclatureContextProxyInterface
{
    public function getStorage(): StorageInterface;

    public function getNomenclatureManager(): NomenclatureManager;
}
