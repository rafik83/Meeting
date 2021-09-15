<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\LoginContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;

class LoginContextProxy implements LoginContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

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
