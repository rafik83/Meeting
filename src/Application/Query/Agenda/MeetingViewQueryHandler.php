<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\View\Agenda\MassUnavailabilityView;
use Proximum\Vimeet\Application\View\Agenda\MeetingView;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class MeetingViewQueryHandler
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * MeetingViewQueryHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository
    ) {
        $this->requestRepository = $requestRepository;
    }

    /**
     * @param MeetingViewQuery $query
     *
     * @return MassUnavailabilityView
     */
    public function handle(MeetingViewQuery $query)
    {
        $fromSheet = $query->meeting->getFromSheet();
        $toSheet   = $query->meeting->getToSheet();

        $sheetMet = null;

        if ($fromSheet !== $query->sheet && $toSheet === $query->sheet
            || $fromSheet == $query->sheet && $toSheet !== $query->sheet
        ) {
            $sheetMet = (null !== $fromSheet ? $fromSheet : $toSheet);
        }

        $requests        = $this->requestRepository->getAllRequestBySheet($query->sheet);
        $matchingRequest = null;

        foreach ($requests as $request) {
            if (($request->getFromSheet() === $query->sheet && $request->getToSheet() === $sheetMet)
                || ($request->getToSheet() === $query->sheet && $request->getFromSheet() === $sheetMet)
            ) {
                $matchingRequest = $request;
            }
        }

        return new MeetingView(
            $query->meeting->getSpot(),
            $sheetMet,
            $query->meeting->getId(),
            null !== $matchingRequest ? $matchingRequest->hasNoPreference($query->sheet) : true
        );
    }
}
