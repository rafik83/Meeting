<?php

namespace Proximum\Vimeet\Behat\Proxy\Meeting;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\Meeting\RequestContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Meeting\RequestManager;

class RequestContextProxy implements RequestContextProxyInterface
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var RequestManager
     */
    private $requestManager;

    /**
     * RequestContextProxy constructor.
     *
     * @param StorageInterface $storage
     * @param RequestManager   $requestManager
     */
    public function __construct(StorageInterface $storage, RequestManager $requestManager)
    {
        $this->storage        = $storage;
        $this->requestManager = $requestManager;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * @return RequestManager
     */
    public function getRequestManager(): RequestManager
    {
        return $this->requestManager;
    }
}
