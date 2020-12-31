<?php

namespace Proximum\Vimeet\Behat\Proxy\Invoice;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\Invoice\PrefixContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Invoice\PrefixManager;

class PrefixContextProxy implements PrefixContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var PrefixManager */
    private $prefixManager;

    /**
     * @param StorageInterface $storage
     * @param PrefixManager    $prefixManager
     */
    public function __construct(StorageInterface $storage, PrefixManager $prefixManager)
    {
        $this->storage = $storage;
        $this->prefixManager = $prefixManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefixManager(): PrefixManager
    {
        return $this->prefixManager;
    }
}
