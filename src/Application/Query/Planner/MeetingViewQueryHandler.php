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
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
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
     * @var array
     */
    private $dispatch = [];

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var Request[]
     */
    private $requests = [];

    /**
     * @var int
     */
    private $recursiveDepth = 0;

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

        foreach ($this->requests as $request) {
            try {
                $sheetsList = [
                    $this->getSheetById($request->getFromSheet()->getId()),
                    $this->getSheetById($request->getToSheet()->getId()),
                ];

                $participantsList = [];

                if ($request->hasFromParticipants()) {
                    /** @var Participant $participant */
                    foreach ($request->getFromParticipantsArray() as $participant) {
                        $participantsList[] = $this->getParticipantById($participant->getId());
                    }
                } else {
                    // No preference on from
                    // If sheet has only one participant
                    if ($request->getFromSheet()->countParticipant() === 1) {
                        /** @var Participant $participant */
                        foreach ($request->getFromSheet()->getParticipants()->toArray() as $participant) {
                            $participantsList[] = $this->getParticipantById($participant->getId());
                        }
                    } else {
                        $this->resetRecursiveDepth();
                        $participantsList[] = $this->getParticipantOfSheet($request->getFromSheet());
                    }
                }

                if ($request->hasToParticipants()) {
                    foreach ($request->getToParticipantsArray() as $participant) {
                        $participantsList[] = $this->getParticipantById($participant->getId());
                    }
                } else {
                    // not preference on to
                    // If sheet has only one participant
                    if ($request->getToSheet()->countParticipant() === 1) {
                        /** @var Participant $participant */
                        foreach ($request->getToSheet()->getParticipants()->toArray() as $participant) {
                            $participantsList[] = $this->getParticipantById($participant->getId());
                        }
                    } else {
                        $this->resetRecursiveDepth();
                        $participantsList[] = $this->getParticipantOfSheet($request->getToSheet());
                    }
                }

                $meetingViews[] = new MeetingView(
                    $request->getId(),
                    $sheetsList,
                    $participantsList
                );
            } catch (SheetNotFoundException $exception) {
                // In case of a sheet not in catalog but with meeting request
                continue;
            }
        }

        return $meetingViews;
    }

    /**
     * Reset the recursive depth
     */
    private function resetRecursiveDepth()
    {
        $this->recursiveDepth = 0;
    }

    /**
     * @param MeetingViewQuery $query
     */
    private function setUp(MeetingViewQuery $query)
    {
        $this->indexSheetsById($query);
        $this->indexParticipantsById($query);
        $this->requests = $this->requestRepository->getAllAcceptedByEvent($query->event);
        $this->countRequestBySheet();

        $this->calculateAssignation();
    }

    /**
     * @param MeetingViewQuery $query
     */
    private function indexParticipantsById(MeetingViewQuery $query)
    {
        $slotTotal = count($query->slots);

        foreach ($query->participants as $participant) {
            // Index participant by id
            $this->participants[$participant->id] = $participant;

            // Calculate percentage for dispatch
            $usersSlots = $slotTotal - count($participant->unavailabilityList);

            if ($usersSlots < 0) {
                $usersSlots = 0;
            }

            $percentage = ($usersSlots * 100) / $slotTotal;

            $this->dispatch[$participant->sheet->id]['participant'][$participant->id]['ppu'] = $percentage;
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

    /**
     * Count the request with no preference by sheet
     */
    private function countRequestBySheet()
    {
        foreach ($this->requests as $request) {
            if (!$request->hasFromParticipants()) {
                if (!isset($this->dispatch[$request->getFromSheet()->getId()]['request'])) {
                    $this->dispatch[$request->getFromSheet()->getId()]['request'] = 1;
                } else {
                    $this->dispatch[$request->getFromSheet()->getId()]['request']++;
                }
            }

            if (!$request->hasToParticipants()) {
                if (!isset($this->dispatch[$request->getToSheet()->getId()]['request'])) {
                    $this->dispatch[$request->getToSheet()->getId()]['request'] = 1;
                } else {
                    $this->dispatch[$request->getToSheet()->getId()]['request']++;
                }
            }
        }
    }

    /**
     * This function has the goal of calculate and set the PPU and NRAP of each participant
     */
    private function calculateAssignation()
    {
        /*
         * Calculation rule:
         *
         * (slot where the user is available) * 100 / (number of slots of the event) = percentage of presence of the user (PPU)
         *
         *
         * PPU * total of request / (∑ PPU) = number of request to assign to this participant (NRAP)
         */

        foreach ($this->sheets as $sheet) {
            $sumPPU = array_reduce(
                $this->dispatch[$sheet->id]['participant'],
                function ($carry, $participantInfo) {
                    return $carry + $participantInfo['ppu'];
                },
                0
            );

            foreach ($this->dispatch[$sheet->id]['participant'] as $key => $participantInfo) {
                // Avoid divided by 0
                if ($sumPPU === 0) {
                    $this->dispatch[$sheet->id]['participant'][$key]['nrap']     = 0;
                    $this->dispatch[$sheet->id]['participant'][$key]['assigned'] = 0;
                } else {
                    if (!isset($this->dispatch[$sheet->id]['request'])) {
                        $this->dispatch[$sheet->id]['participant'][$key]['nrap'] = 0;
                    } else {
                        $this->dispatch[$sheet->id]['participant'][$key]['nrap'] = ($this->dispatch[$sheet->id]['participant'][$key]['ppu'] * $this->dispatch[$sheet->id]['request']) / $sumPPU;
                    }

                    // set the number of request assigned to 0
                    $this->dispatch[$sheet->id]['participant'][$key]['assigned'] = 0;
                }
            }
        }
    }

    /**
     * @param Sheet $sheet
     *
     * @return ParticipantView
     *
     * @throws ParticipantNotFoundException
     */
    private function getParticipantOfSheet(Sheet $sheet)
    {
        $this->recursiveDepth++;
        $randomParticipantKey = array_rand($this->dispatch[$sheet->getId()]['participant']);
        $participantInfo      = $this->dispatch[$sheet->getId()]['participant'][$randomParticipantKey];

        // Avoid taking the participant with 0 nrap as >= can take it
        if ($participantInfo['nrap'] !== 0
            && $participantInfo['nrap'] >= $participantInfo['assigned']
            || $this->recursiveDepth > 200
        ) {
            // Increment the number of meeting assigned to the participant
            $this->dispatch[$sheet->getId()]['participant'][$randomParticipantKey]['assigned']++;

            return $this->getParticipantById($randomParticipantKey);
        }

        // Otherwise, recursivity
        return $this->getParticipantOfSheet($sheet);
    }
}
