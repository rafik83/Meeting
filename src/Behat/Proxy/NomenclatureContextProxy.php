<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\NomenclatureContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\NomenclatureManager;

class NomenclatureContextProxy implements NomenclatureContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var NomenclatureManager */
    private $nomenclatureManager;

    public function __construct(StorageInterface $storage, NomenclatureManager $nomenclatureManager)
    {
        $this->storage = $storage;
        $this->nomenclatureManager = $nomenclatureManager;
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function getNomenclatureManager(): NomenclatureManager
    {
        return $this->nomenclatureManager;
    }
}
