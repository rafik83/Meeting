<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Denormalizer\Planner;

use Proximum\Vimeet\Application\View\Planner\Result\PlannerResult;
use Proximum\Vimeet\Application\View\Planner\Result\MeetingResult;
use Proximum\Vimeet\Application\View\Planner\Result\ParticipantResult;
use Proximum\Vimeet\Application\View\Planner\Result\SheetResult;
use Proximum\Vimeet\Application\View\Planner\Result\SlotResult;
use Proximum\Vimeet\Application\View\Planner\Result\SpotResult;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PlannerDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /** @var SheetResult[] */
    private $sheetList = [];

    /** @var ParticipantResult[] */
    private $participantList = [];

    /** @var SpotResult[] */
    private $spotList = [];

    /** @var SlotResult[] */
    private $slotList = [];

    /** @var MeetingResult[] */
    private $meetingList = [];

    const TYPE_PARTICIPANT = 'type_participant';
    const TYPE_SLOT        = 'type_slot';
    CONST TYPE_SPOT        = 'type_spot';
    CONST TYPE_SHEET       = 'type_sheet';

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (!is_array($data)
            || !isset($data['meetingList'])
            || !isset($data['sheetList'])
            || !isset($data['dayList'])
            || !isset($data['slotList'])
            || !isset($data['spotList'])
            || !isset($data['participantList'])
            || !isset($data['typeList'])
            || !isset($data['typePriorityList'])
            || !isset($data['meetingList']['Meeting'])
            || !isset($data['sheetList']['Sheet'])
            || !isset($data['participantList']['Participant'])
            || !isset($data['spotList']['Spot'])
            || !isset($data['slotList']['Slot'])
        ) {
            throw new \InvalidArgumentException('Missing a required node');
        }

        $this->handleData($data); // Create the list of element from the nodes
        $this->createMeetings($data); // Create the meetings after the creation of the list to get the objects

        if (isset($context['object_to_populate']) && $context['object_to_populate'] instanceof PlannerResult) {
            $plannerResult = $context['object_to_populate'];
        } else {
            $plannerResult = new PlannerResult();
        }

        $plannerResult->meetings     = $this->meetingList;
        $plannerResult->sheets       = $this->sheetList;
        $plannerResult->participants = $this->participantList;
        $plannerResult->slots        = $this->slotList;
        $plannerResult->spots        = $this->spotList;

        return $plannerResult;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === PlannerResult::class && $format === 'xml';
    }

    private function createMeetings(array $data)
    {
        if (is_array($data['meetingList']['Meeting'])) {
            foreach ($data['meetingList']['Meeting'] as $meeting) {
                $this->createMeeting($meeting);
            }
        }
    }

    /**
     * @param array $data
     */
    private function handleData(array $data)
    {
        if (is_array($data['meetingList']['Meeting'])) {
            foreach ($data['meetingList']['Meeting'] as $meeting) {
                $this->handleMeeting($meeting);
            }
        }

        if (is_array($data['slotList']['Slot'])) {
            foreach ($data['slotList']['Slot'] as $slot) {
                $this->handleSlot($slot);
            }
        }

        if (is_array($data['spotList']['Spot'])) {
            foreach ($data['spotList']['Spot'] as $spot) {
                $this->handleSpot($spot);
            }
        }

        if (is_array($data['participantList']['Participant'])) {
            foreach ($data['participantList']['Participant'] as $participant) {
                $this->handleParticipant($participant);
            }
        }

        if (is_array($data['sheetList']['Sheet'])) {
            foreach ($data['sheetList']['Sheet'] as $sheet) {
                $this->handleSheet($sheet);
            }
        }
    }

    /**
     * @param array $spot
     *
     * @return null|SpotResult
     */
    private function handleSpot(array $spot)
    {
        $spotResult = null;

        if (isset($spot['@reference'])) {
            if (isset($this->spotList[$spot['@reference']])) {
                return $this->spotList[$spot['@reference']];
            }

            return $spotResult;
        }

        if (isset($spot['@id']) && isset($spot['id'])) {
            $spotResult = new SpotResult($spot['id']);
            $this->spotList[$spot['@id']] = $spotResult;
        }

        if (isset($spot['sheetList']) && isset($spot['sheetList']['Sheet'])) {
            if ($this->isSingle($spot['sheetList']['Sheet'])) {
                $this->handleSheet($spot['sheetList']['Sheet']);
            } else {
                foreach ($spot['sheetList']['Sheet'] as $sheet) {
                    $this->handleSheet($sheet);
                }
            }
        }

        return $spotResult;
    }

    /**
     * @param array $participant
     *
     * @return null|ParticipantResult
     */
    private function handleParticipant(array $participant)
    {
        if (isset($participant['@reference'])) {
            if (isset($this->participantList[$participant['@reference']])) {
                return $this->participantList[$participant['@reference']];
            }

            return null;
        }

        if (isset($participant['@id']) && isset($participant['id'])) {
            $participantResult = new ParticipantResult($participant['id']);
            $this->participantList[$participant['@id']] = $participantResult;

            if (isset($participant['sheet'])) {
                $this->handleSheet($participant['sheet']);
            }

            if (isset($participant['unavailabilityList'])
                && is_array($participant['unavailabilityList'])
                && isset($participant['unavailabilityList']['Slot'])
            ) {
                if ($this->isSingle($participant['unavailabilityList']['Slot'])) {
                    $this->handleSlot($participant['unavailabilityList']['Slot']);
                } else {
                    foreach ($participant['unavailabilityList']['Slot'] as $slot) {
                        $this->handleSlot($slot);
                    }
                }

            }

            return $participantResult;
        }

        return null;
    }

    /**
     * @param array $slot
     *
     * @return null|SlotResult
     */
    private function handleSlot(array $slot)
    {
        if (isset($slot['@reference'])) {
            if (isset($this->slotList[$slot['@reference']])) {
                return $this->slotList[$slot['@reference']];
            }

            return null;
        }

        if (isset($slot['@id']) && isset($slot['id'])) {
            $slotResult = new SlotResult($slot['id']);
            $this->slotList[$slot['@id']] = $slotResult;

            return $slotResult;
        }

        return null;
    }

    /**
     * @param array $sheet
     *
     * @return null|SheetResult
     */
    private function handleSheet(array $sheet)
    {
        if (isset($sheet['@reference'])) {
            if (isset($this->sheetList[$sheet['@reference']])) {
                return $this->sheetList[$sheet['@reference']];
            }

            return null;
        }

        if (isset($sheet['@id']) && isset($sheet['id'])) {
            $sheetResult = new SheetResult($sheet['id']);
            $this->sheetList[$sheet['@id']] = $sheetResult;

            return $sheetResult;
        }

        return null;
    }

    /**
     * This method is used to handle the data potentially initiated in the meeting
     * It is not the method that instanciate the meetingList
     * as the element inside can be not yet added to the other list
     *
     * @param array $meeting
     */
    private function handleMeeting(array $meeting)
    {
        if (isset($meeting['@reference'])) {
            return;
        }

        if (isset($meeting['sheetList'])
            && is_array($meeting['sheetList'])
            && isset($meeting['sheetList']['Sheet'])
            && is_array($meeting['sheetList']['Sheet'])
        ) {
            if (!$this->isSingle($meeting['sheetList']['Sheet'])) {
                foreach ($meeting['sheetList']['Sheet'] as $sheet) {
                    $this->handleSheet($sheet);
                }
            } else {
                $this->handleSheet($meeting['sheetList']['Sheet']);
            }
        }

        if (isset($meeting['participantList'])
            && is_array($meeting['participantList'])
            && isset($meeting['participantList']['Participant'])
            && is_array($meeting['participantList']['Participant'])
        ) {
            if (!$this->isSingle($meeting['participantList']['Participant'])) {
                foreach ($meeting['participantList']['Participant'] as $participant) {
                    $this->handleParticipant($participant);
                }
            } else {
                $this->handleParticipant($meeting['participantList']['Participant']);
            }
        }

        if (isset($meeting['spot'])) {
            $this->handleSpot($meeting['spot']);
        }

        if (isset($meeting['slot'])) {
            $this->handleSlot($meeting['slot']);
        }
    }

    /**
     * This method add the meetingResult to the meetingList
     * It should be called after the handleData method that build the other list
     * needed for the creation of the meetingResult
     *
     * @param array $meeting
     */
    private function createMeeting(array $meeting)
    {
        if (isset($meeting['@reference'])) {
            return;
        }

        $meetingResult = new MeetingResult();

        if (isset($meeting['id'])) {
            $meetingResult->requestId = $meeting['id'];
        }

        if (isset($meeting['sheetList'])
            && is_array($meeting['sheetList'])
            && isset($meeting['sheetList']['Sheet'])
            && is_array($meeting['sheetList']['Sheet'])
        ) {
            $loopIndex = 1;

            // The first sheet is the fromSheet, the second is the toSheet
            foreach ($meeting['sheetList']['Sheet'] as $sheet) {
                $sheetResult = $this->handleSheet($sheet);

                if ($loopIndex === 1) {
                    $meetingResult->sheetFrom = $sheetResult;
                } elseif ($loopIndex === 2) {
                    $meetingResult->sheetTo = $sheetResult;
                }

                $loopIndex++;
            }
        }

        if (isset($meeting['participantList'])
            && is_array($meeting['participantList'])
            && isset($meeting['participantList']['Participant'])
            && is_array($meeting['participantList']['Participant'])
        ) {
            foreach ($meeting['participantList']['Participant'] as $participant) {
                $participantResult = $this->handleParticipant($participant);

                if ($participantResult !== null) { // this case should not happened but it does not cost much to check
                    $meetingResult->addParticipant($participantResult);
                }
            }
        }

        if (isset($meeting['spot'])) {
            $spotResult = $this->handleSpot($meeting['spot']);

            if (null !== $spotResult) {
                $meetingResult->spot = $spotResult;
            }
        }

        if (isset($meeting['slot'])) {
            $slotResult = $this->handleSlot($meeting['slot']);

            if (null !== $slotResult) {
                $meetingResult->slot = $slotResult;
            }
        }

        $this->meetingList[] = $meetingResult;
    }

    /**
     * @param array $element
     *
     * @return bool
     */
    private function isSingle(array $element)
    {
        if (isset($element['@reference']) || isset($element['@id'])) {
            return true;
        }

        return false;
    }
}
