<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Meeting;

use Proximum\Vimeet\Application\Query\Dashboard\View\DashboardRequestView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;

interface RequestRepositoryInterface
{
    const ORDER_BY_CREATE_AT_ASC         = 'created_at_asc';
    const ORDER_BY_CREATE_AT_DESC        = 'created_at_desc';
    const ORDER_BY_STATE_UPDATED_AT_ASC  = 'state_updated_at_asc';
    const ORDER_BY_STATE_UPDATED_AT_DESC = 'state_updated_at_desc';

    /**
     * @param Request $request
     */
    public function add(Request $request);

    /**
     * @param Request $request
     */
    public function set(Request $request);

    /**
     * @param Request $request
     */
    public function remove(Request $request);

    /**
     * @param Request $request
     *
     * @return Request
     */
    public function getRequest(Request $request);

    /**
     * @param Sheet $sheet
     *
     * @return Request[]
     */
    public function getRequestSentBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return Request[]
     */
    public function getApprovedRequestSentBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return Request[]
     */
    public function getPropositionReceivedBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return Request[]
     */
    public function getApprovedPropositionReceivedBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     * @param array $filters
     * @param array $slotsToFilter
     *
     * @return Request[]
     */
    public function getAllRequestBySheet(Sheet $sheet, array $filters = [], array $slotsToFilter = []): array;

    public function getAllRequestBySheetAndSheets(
        Sheet $sheet,
        array $sheets,
        array $filters = [],
        array $slotsToFilter = []
    ): array;

    /**
     * @param Sheet $sheet
     *
     * @return Request[]
     */
    public function getApprovedAndRefusedRequestBySheet(Sheet $sheet): array;

    /**
     * @param Event $event
     *
     * @return int
     */
    public function countAllByEvent(Event $event): int;

    /**
     * @param Event $event
     *
     * @return int
     */
    public function countApprovedByEvent(Event $event): int;

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countBySheetWithPriority(Sheet $sheet): int;

    /**
     * @param Event $event
     *
     * @return int
     */
    public function countPendingByEvent(Event $event): int;

    /**
     * @param Event $event
     *
     * @return int
     */
    public function countRefusedByEvent(Event $event): int;

    /**
     * @param Event  $event
     * @param int    $page
     * @param int    $limit
     * @param string $locale
     * @param array  $filter
     *
     * @return PaginatedResult
     */
    public function findByEventAndFilterByState(Event $event, $page, $limit, $locale, array $filter = []);

    /**
     * @param Event $event
     * @param int   $page
     * @param int   $limit
     *
     * @return Request[]
     */
    public function findByEventWithHydratationOfElement(Event $event, int $page, int $limit): array;

    /**
     * This method is used to hydrate the participants of the requests to avoid multiple calls
     *
     * @param Request[] $requests
     *
     * @return Request[]
     */
    public function hydrateParticipants(array $requests): array;

    /**
     * @param Event $event
     *
     * @return Request[]
     */
    public function getAllAcceptedByEvent(Event $event);

    /**
     * @param Sheet $one
     * @param Sheet $another
     * @param array $state
     *
     * @return Request[]
     */
    public function getRequestBetweenSheetsWithStates(Sheet $one, Sheet $another, array $state);

    /**
     * @param Sheet $one
     * @param Sheet $another
     *
     * @return Request|null
     */
    public function getRequestBetweenSheets(Sheet $one, Sheet $another);

    /**
     * @param Sheet $one
     * @param Sheet $another
     *
     * @return bool
     */
    public function hasRequestBetweenSheets(Sheet $one, Sheet $another): bool;

    /**
     * @param Event            $event
     * @param Sheet[]          $sheets
     * @param Sheet[]          $sheetsMet
     * @param string|null      $state
     * @param string|null      $type
     * @param User|string|null $user
     *
     * @return Request[]
     */
    public function getRequestsOfSheetsWithSheets(
        Event $event,
        array $sheets,
        array $sheetsMet,
        $state = null,
        $type = null,
        $user = null
    );

    /**
     * @param Event $event
     * @param Type  $type
     *
     * @return Request[]
     */
    public function getApprovedByType(Event $event, Type $type): array;

    /**
     * @param Event            $event
     * @param Sheet[]          $sheets
     * @param Sheet[]          $sheetsMet
     * @param string|null      $state
     * @param string|null      $type
     * @param User|string|null $user
     *
     * @return int
     */
    public function countRequestOfSheetsWithSheets(
        Event $event,
        array $sheets,
        array $sheetsMet,
        $state = null,
        $type = null,
        $user = null
    );

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return Request[]
     */
    public function getRequestsPlacedByEventAndUser(Event $event, User $user);

    /**
     * @param Sheet $sheet
     * @param array $filters
     * @param array $slotsToFilter
     *
     * @return int
     */
    public function countSheetState(Sheet $sheet, array $filters = [], array $slotsToFilter = []);

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countApprovedRequestSentBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countRefusedRequestSentBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countPendingRequestSentBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countApprovedPropositionReceivedBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countRefusedPropositionReceivedBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     * @param bool  $attending
     *
     * @return int
     */
    public function countPendingPropositionReceivedBySheet(Sheet $sheet, $attending = true);

    /**
     * @param Sheet $sheet
     *
     * @return Request[]
     */
    public function getPendingPropositionReceivedBySheet(Sheet $sheet);

    /**
     * @param Sheet $toSheet
     * @param int[] $slotIds array of MeetingSlot id
     *
     * @return int
     */
    public function countPendingPropositionReceivedBySheetWithAvailableFromSheet(Sheet $toSheet, array $slotIds): int;

    /**
     * @param Sheet $toSheet
     * @param array $slotIds
     *
     * @return int
     */
    public function countPendingPropositionReceivedBySheetWithAvailableToSheet(Sheet $toSheet, array $slotIds): int;

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function hasPendingPropositionReceivedBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countRequestSentBySheet(Sheet $sheet);

    /**
     * @param Event $event
     * @param array $sheets
     *
     * @return array of ['countRequest' => int, 'sheetId' => int]
     */
    public function countApprovedRequestBySheets(Event $event, array $sheets): array;

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function hasRequestSentBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countPropositionReceivedBySheet(Sheet $sheet);

    /**
     * @param Sheet  $sheet
     * @param string $state
     *
     * @return Request[]
     */
    public function getUnassignedRequestsBySheetAndEvent(Sheet $sheet, $state);

    /**
     * @param Sheet[] $sheets
     *
     * @return Request[]
     */
    public function getUnallocatedRequestForSheets(array $sheets);

    /**
     * @param Request $request
     */
    public function update(Request $request);

    /**
     * @param $sheet
     *
     * @return Request[]
     */
    public function findApproved(Sheet $sheet): array;

    /**
     * @param Participant $participant
     *
     * @return bool
     */
    public function participantIsAssignedToAccepted(Participant $participant);

    /**
     * @param Participant $participant
     *
     * @return bool
     */
    public function hasAssignedRequestByParticipant(Participant $participant);

    /**
     * @param Event    $event
     * @param Sheet[]  $sheets
     * @param string[] $states
     * @param bool     $withoutMeeting
     *
     * @return Request[]
     */
    public function findBySheets(Event $event, array $sheets, array $states, bool $withoutMeeting): array;

    public function hasApprovedMeetingRequest(Sheet $sheet, Sheet $sheetMet): bool;

    /**
     * @param Event $event
     *
     * @return Request[]
     */
    public function findApprovedAndPrioritizedWithoutMeeting(Event $event): array;

    /**
     * @param Event $event
     *
     * @return DashboardRequestView[]
     */
    public function getDashboardRequestViewsByEvent(Event $event): array;
}
