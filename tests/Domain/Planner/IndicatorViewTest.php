<?php

namespace Proximum\Vimeet\Tests\Domain\Planner;

use Proximum\Vimeet\Domain\Planner\IndicatorView;
use PHPUnit\Framework\TestCase;

class IndicatorViewTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function test__construct(
        int $slotTotal,
        int $participantsCount,
        int $unavailabilitiesCount,
        int $sheetsPlanningQuantity,
        int $meetingRequestsCount,
        int $pendingPropositionCount,
        int $massUnavailabilitiesCount,
        ?int $numberOfMeetingsPerPlanning,
        ?int $numberMaxOfMeetingsPerSheet,
        int $expectedMaxMeetingAvailable,
        int $expectedAvailableSlotsCount,
        int $expectedSlotCount,
        int $expectedSlotsParticipantsCount,
        int $expectedPossibleMeetingsQuantity,
        int $expectedUsableSlots
    ) {
        $indicatorView = new IndicatorView(
            $slotTotal,
            $participantsCount,
            $unavailabilitiesCount,
            $sheetsPlanningQuantity,
            $meetingRequestsCount,
            $pendingPropositionCount,
            $massUnavailabilitiesCount,
            $numberOfMeetingsPerPlanning,
            $numberMaxOfMeetingsPerSheet
        );

        $this->assertEquals($expectedSlotCount, $indicatorView->slotCount);
        $this->assertEquals($expectedSlotsParticipantsCount, $indicatorView->slotsParticipantsCount);
        $this->assertEquals($expectedMaxMeetingAvailable, $indicatorView->maxMeetingAvailable);
        $this->assertEquals($expectedAvailableSlotsCount, $indicatorView->availableSlotsCount);
        $this->assertEquals($expectedPossibleMeetingsQuantity, $indicatorView->possibleMeetingsQuantity);
        $this->assertEquals($expectedUsableSlots, $indicatorView->usableSlots);
    }

    public function dataProvider()
    {
        return [
            'one_participant_with_no_unavailabilitie' => [
                'slotTotal' => 7,
                'participantsCount' => 1,
                'unavailabilitiesCount' => 0,
                'sheetsPlanningQuantity' => 1,
                'meetingRequestsCount' => 20,
                'pendingPropositionCount' => 2,
                'massUnavailabilitiesCount' => 0,
                'numberOfMeetingsPerPlanning' => null,
                'numberMaxOfMeetingsPerSheet' => null,
                'expectedMaxMeetingAvailable' => 7,
                'expectedAvailableSlotsCount' => 7,
                'expectedSlotCount' => 7,
                'expectedSlotsParticipantsCount' => 7,
                'expectedPossibleMeetingsQuantity' => 7,
                'expectedUsableSlots' => 7,
            ],
            'three_participants_with_unavailabilities_and_mass_unavailabilities' => [
                'slotTotal' => 7,
                'participantsCount' => 3,
                'unavailabilitiesCount' => 2,
                'sheetsPlanningQuantity' => 3,
                'meetingRequestsCount' => 10,
                'pendingPropositionCount' => 2,
                'massUnavailabilitiesCount' => 6,
                'numberOfMeetingsPerPlanning' => null,
                'numberMaxOfMeetingsPerSheet' => null,
                'expectedMaxMeetingAvailable' => 15,
                'expectedAvailableSlotsCount' => 19,
                'expectedSlotCount' => 21,
                'expectedSlotsParticipantsCount' => 21,
                'expectedPossibleMeetingsQuantity' => 10,
                'expectedUsableSlots' => 19,
            ],
            'three_participants_and_limited_number_of_meetings_per_planning' => [
                'slotTotal' => 7,
                'participantsCount' => 3,
                'unavailabilitiesCount' => 2,
                'sheetsPlanningQuantity' => 3,
                'meetingRequestsCount' => 10,
                'pendingPropositionCount' => 2,
                'massUnavailabilitiesCount' => 6,
                'numberOfMeetingsPerPlanning' => 4,
                'numberMaxOfMeetingsPerSheet' => null,
                'expectedMaxMeetingAvailable' => 12,
                'expectedAvailableSlotsCount' => 19,
                'expectedSlotCount' => 21,
                'expectedSlotsParticipantsCount' => 21,
                'expectedPossibleMeetingsQuantity' => 10,
                'expectedUsableSlots' => 19,
            ],
            'one_participant_with_max_number_meetings_per_sheet_bigger_than_maxMeetingAvailable' => [
                'slotTotal' => 7,
                'participantsCount' => 1,
                'unavailabilitiesCount' => 0,
                'sheetsPlanningQuantity' => 1,
                'meetingRequestsCount' => 20,
                'pendingPropositionCount' => 2,
                'massUnavailabilitiesCount' => 0,
                'numberOfMeetingsPerPlanning' => 2,
                'numberMaxOfMeetingsPerSheet' => 8,
                'expectedMaxMeetingAvailable' => 2,
                'expectedAvailableSlotsCount' => 7,
                'expectedSlotCount' => 7,
                'expectedSlotsParticipantsCount' => 7,
                'expectedPossibleMeetingsQuantity' => 2,
                'expectedUsableSlots' => 7,
            ],
            'one_participant_with_max_number_meetings_per_sheet_equals_to_maxMeetingAvailable' => [
                'slotTotal' => 7,
                'participantsCount' => 1,
                'unavailabilitiesCount' => 0,
                'sheetsPlanningQuantity' => 2,
                'meetingRequestsCount' => 20,
                'pendingPropositionCount' => 2,
                'massUnavailabilitiesCount' => 0,
                'numberOfMeetingsPerPlanning' => 5,
                'numberMaxOfMeetingsPerSheet' => 6,
                'expectedMaxMeetingAvailable' => 6,
                'expectedAvailableSlotsCount' => 7,
                'expectedSlotCount' => 14,
                'expectedSlotsParticipantsCount' => 7,
                'expectedPossibleMeetingsQuantity' => 6,
                'expectedUsableSlots' => 6,
            ],
            'three_participant_with_max_number_meetings_per_sheet_equals_to_maxMeetingAvailable' => [
                'slotTotal' => 7,
                'participantsCount' => 3,
                'unavailabilitiesCount' => 0,
                'sheetsPlanningQuantity' => 2,
                'meetingRequestsCount' => 20,
                'pendingPropositionCount' => 2,
                'massUnavailabilitiesCount' => 0,
                'numberOfMeetingsPerPlanning' => 5,
                'numberMaxOfMeetingsPerSheet' => 9,
                'expectedMaxMeetingAvailable' => 9,
                'expectedAvailableSlotsCount' => 21,
                'expectedSlotCount' => 14,
                'expectedSlotsParticipantsCount' => 21,
                'expectedPossibleMeetingsQuantity' => 9,
                'expectedUsableSlots' => 9,
            ],
            'three_participant_with_max_number_meetings_per_sheet_bigger_than_maxMeetingAvailable' => [
                'slotTotal' => 7,
                'participantsCount' => 3,
                'unavailabilitiesCount' => 0,
                'sheetsPlanningQuantity' => 2,
                'meetingRequestsCount' => 20,
                'pendingPropositionCount' => 2,
                'massUnavailabilitiesCount' => 0,
                'numberOfMeetingsPerPlanning' => 2,
                'numberMaxOfMeetingsPerSheet' => 6,
                'expectedMaxMeetingAvailable' => 4,
                'expectedAvailableSlotsCount' => 21,
                'expectedSlotCount' => 14,
                'expectedSlotsParticipantsCount' => 21,
                'expectedPossibleMeetingsQuantity' => 4,
                'expectedUsableSlots' => 6,
            ],
        ];
    }
}
