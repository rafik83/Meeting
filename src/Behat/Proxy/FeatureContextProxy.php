<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\FeatureContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;

class FeatureContextProxy implements FeatureContextProxyInterface
{
    /** @var StorageInterface */
    public $storage;

    /**
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        return $this->storage;
    }
}
