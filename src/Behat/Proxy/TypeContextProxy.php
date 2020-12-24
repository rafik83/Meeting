<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\TypeContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\TypeManager;

class TypeContextProxy implements TypeContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var TypeManager */
    private $typeManager;

    /**
     * @param StorageInterface $storage
     * @param TypeManager      $typeManager
     */
    public function __construct(StorageInterface $storage, TypeManager $typeManager)
    {
        $this->storage    = $storage;
        $this->typeManager = $typeManager;
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
    public function getTypeManager()
    {
        return $this->typeManager;
    }
}
