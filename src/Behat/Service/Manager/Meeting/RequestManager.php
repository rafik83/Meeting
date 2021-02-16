<?php

namespace Proximum\Vimeet\Behat\Service\Manager\Meeting;

use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class RequestManager
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * RequestManager constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(RequestRepositoryInterface $requestRepository)
    {
        $this->requestRepository = $requestRepository;
    }
}
