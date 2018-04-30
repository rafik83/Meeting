<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
