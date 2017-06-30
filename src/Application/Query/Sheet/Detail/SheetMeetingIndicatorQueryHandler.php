<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Detail;

use Proximum\Vimeet\Application\View\Sheet\Details\SheetMeetingIndicatorView;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class SheetMeetingIndicatorQueryHandler
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * SheetMeetingIndicatorQueryHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(RequestRepositoryInterface $requestRepository)
    {
        $this->requestRepository = $requestRepository;
    }

    /**
     * @param SheetMeetingIndicatorQuery $query
     *
     * @return SheetMeetingIndicatorView
     */
    public function handle(SheetMeetingIndicatorQuery $query): SheetMeetingIndicatorView
    {
        return new SheetMeetingIndicatorView(
            $this->requestRepository->countApprovedRequestSentBySheet($query->sheet),
            $this->requestRepository->countPendingRequestSentBySheet($query->sheet),
            $this->requestRepository->countRefusedRequestSentBySheet($query->sheet),
            $this->requestRepository->countApprovedPropositionReceivedBySheet($query->sheet),
            $this->requestRepository->countPendingPropositionReceivedBySheet($query->sheet, false),
            $this->requestRepository->countRefusedPropositionReceivedBySheet($query->sheet)
        );
    }
}
