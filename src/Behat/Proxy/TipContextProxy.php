<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\TipContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\TipManager;

class TipContextProxy implements TipContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var TipManager */
    private $tipManager;

    /**
     * @param StorageInterface $storage
     * @param TipManager       $tipManager
     */
    public function __construct(StorageInterface $storage, TipManager $tipManager)
    {
        $this->storage    = $storage;
        $this->tipManager = $tipManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * {@inheritdoc}
     */
    public function getTipManager()
    {
        return $this->tipManager;
    }
}
