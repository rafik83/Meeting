<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\Exception\Planner\ParticipantNotFoundException;
use Proximum\Vimeet\Application\Exception\Planner\SheetNotFoundException;
use Proximum\Vimeet\Application\View\Planner\MeetingView;
use Proximum\Vimeet\Application\View\Planner\ParticipantView;
use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class MeetingViewQueryHandler
{
    /**
     * @var SheetView[]
     */
    private $sheets;

    /**
     * @var ParticipantView[]
     */
    private $participants;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(RequestRepositoryInterface $requestRepository)
    {
        $this->requestRepository = $requestRepository;
    }

    /**
     * @param MeetingViewQuery $query
     *
     * @return MeetingView[]
     */
    public function handle(MeetingViewQuery $query)
    {
        $this->setUp($query);

        $meetingViews = [];
        $requests     = $this->requestRepository->getAllAcceptedByEvent($query->event);

        foreach ($requests as $request) {
            $sheetsList = [
                $this->getSheetById($request->getFromSheet()->getId()),
                $this->getSheetById($request->getToSheet()->getId()),
            ];

            $participantsList = [];

            if ($request->hasFromParticipants()) {
                foreach ($request->getFromParticipantsArray() as $participant) {
                    $participantsList[] = $this->getParticipantById($participant->getId());
                }
            } else {
                // No preference on from
            }

            if ($request->hasToParticipants()) {
                foreach ($request->getToParticipantsArray() as $participant) {
                    $participantsList[] = $this->getParticipantById($participant->getId());
                }
            } else {
                // not preference on to
            }

            $meetingViews[] = new MeetingView(
                $request->getId(),
                $sheetsList,
                $participantsList
            );
        }

        return $meetingViews;
    }

    /**
     * @param MeetingViewQuery $query
     */
    private function setUp(MeetingViewQuery $query)
    {
        $this->indexSheetsById($query);
        $this->indexParticipantsById($query);
    }

    /**
     * @param MeetingViewQuery $query
     */
    private function indexParticipantsById(MeetingViewQuery $query)
    {
        foreach ($query->participants as $participant) {
            $this->participants[$participant->id] = $participant;
        }
    }

    /**
     * @param MeetingViewQuery $query
     */
    private function indexSheetsById(MeetingViewQuery $query)
    {
        foreach ($query->sheets as $sheet) {
            $this->sheets[$sheet->id] = $sheet;
        }
    }

    /**
     * @param int $id
     *
     * @return SheetView
     *
     * @throws SheetNotFoundException
     */
    private function getSheetById($id)
    {
        if (isset($this->sheets[$id])) {
            return $this->sheets[$id];
        }

        throw new SheetNotFoundException(sprintf('Sheet of id %s was not found', $id));
    }

    /**
     * @param int $id
     *
     * @return ParticipantView
     *
     * @throws ParticipantNotFoundException
     */
    private function getParticipantById($id)
    {
        if (isset($this->participants[$id])) {
            return $this->participants[$id];
        }

        throw new ParticipantNotFoundException(sprintf('Participant of id %s was not found', $id));
    }
}
