<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\AvailableSheets;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class RequestApprovedAndRefusedQueryHandler
{
    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /**
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(RequestRepositoryInterface $requestRepository)
    {
        $this->requestRepository = $requestRepository;
    }

    /**
     * @param RequestApprovedAndRefusedQuery $query
     *
     * @return Request[]
     */
    public function handle(RequestApprovedAndRefusedQuery $query): array
    {
        $requestsApproved = $this->requestRepository->getAllRequestBySheet($query->sheet, [
            'state' => Request::STATE_APPROVED,
        ]);
        $requestRefused = $this->requestRepository->getAllRequestBySheet($query->sheet, [
            'state' => Request::STATE_REFUSED,
        ]);

        return array_merge($requestsApproved, $requestRefused);
    }
}
