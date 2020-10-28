<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventInterface;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\View\SheetView;
use Proximum\Vimeet\Domain\View\Spot\Import\SheetView as ImportSheetView;

interface SheetRepositoryInterface
{
    /**
     * @param Sheet $sheet
     */
    public function add(Sheet $sheet);

    /**
     * @param Sheet $sheet
     */
    public function set(Sheet $sheet);

    /**
     * @param int[] $ids
     * @param bool  $state
     */
    public function updateInCatalogBySheetsId(array $ids, $state);

    /**
     * @param int[] $ids
     * @param bool  $state
     */
    public function updateEnableStateBySheetsId(array $ids, $state);

    /**
     * @param int[]  $ids
     * @param string $state
     */
    public function updateStateBySheetsId(array $ids, $state);

    /**
     * @param int[] $ids array of Sheet id
     */
    public function refuseBySheetsId(array $ids);

    /**
     * @param int[] $ids
     * @param Admin $admin
     */
    public function batchAssignBySheetsId(array $ids, Admin $admin);

    /**
     * @param int[]  $ids
     * @param string $state
     */
    public function updateValidationState(array $ids, $state);

    /**
     * @param Event $event
     *
     * @return Sheet[]
     */
    public function getByEvent(Event $event): array;

    /**
     * @param Event $event
     *
     * @return SheetView[]
     */
    public function getByEventAndOrderedByTitle(Event $event): array;

    /**
     * @param Event   $event
     * @param Sheet[] $excludedSheets
     *
     * @return Sheet[]
     */
    public function getSheetsInCatalogByEvent(Event $event, array $excludedSheets = []): array;

    /**
     * @param Event       $event
     * @param Type[]      $types
     * @param MeetingSlot $slot
     * @param Sheet[]     $excludedSheets
     *
     * @return int
     *
     * @deprecated This request is not used anymore.
     */
    public function countAvailableSheetsInCatalogWithTypesByEvent(
        Event $event,
        array $types = [],
        MeetingSlot $slot,
        array $excludedSheets = []
    ): int;

    /**
     * @param Event $event
     *
     * @return Sheet[]
     */
    public function getByEventWithParticipantsAndOwner(Event $event);

    /**
     * @param Event $event
     *
     * @return array of owner emails with the format:
     *               [
     *               0 => ['email' => 'email0@example.net'],
     *               1 => ['email' => 'email1@example.net'],
     *               ]
     */
    public function getOwnerEmails(Event $event): array;

    /**
     * @param Sheet $sheet
     *
     * @return Sheet[]
     */
    public function getSheetsMetBySheet(Sheet $sheet): array;

    /**
     * @param Sheet $sheet
     *
     * @return Sheet[]
     */
    public function getSheetsWithRequestWithSheet(Sheet $sheet): array;

    /**
     * @param Type $type
     *
     * @return bool
     */
    public function isThereAtLeastOneByType(Type $type);

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return Sheet[]
     */
    public function getSheets(Event $event, $locale);

    /**
     * @param int|User  $user
     * @param int|Event $event
     * @param string    $locale
     *
     * @return SheetView[]
     */
    public function getSheetViewsByUserAndEvent($user, $event, $locale);

    /**
     * @param Event $event
     * @param int   $sheetId
     *
     * @return null|ImportSheetView
     */
    public function getSheetViewByEventAndId(Event $event, int $sheetId): ? ImportSheetView;

    /**
     * Get only enabled sheet by user or user's participant
     *
     * @param User  $user
     * @param Event $event
     *
     * @return Sheet[]
     */
    public function getSheetsByUserAndEvent(User $user, Event $event);

    /**
     * Get only enabled sheet of users on this event
     *
     * @param User[]|int[] $users
     * @param Event $event
     *
     * @return Sheet[]
     */
    public function getSheetsByUsersAndEvent(array $users, Event $event): array;

    /**
     * Get count enabled sheet by user or user's participant
     *
     * @param User  $user
     * @param Event $event
     *
     * @return int
     */
    public function countSheetsByUserAndEvent(User $user, Event $event);

    /**
     * Get all sheets whatever its enabled state by user or user's participant
     *
     * @param User  $user
     * @param Event $event
     *
     * @return Sheet[]
     */
    public function getAllSheetsByUserAndEvent(User $user, Event $event);

    /**
     * @param User           $user
     * @param EventInterface $event
     *
     * @return Sheet[]
     */
    public function getSheetsByUserAndEventWhereUserIsParticipant(User $user, EventInterface $event);

    /**
     * @param User           $user
     * @param EventInterface $event
     *
     * @return bool
     */
    public function isParticipantToEnabledSheet(User $user, EventInterface $event);

    /**
     * @param int $sheetId
     *
     * @return null|Sheet
     */
    public function getSheetById($sheetId);

    /**
     * @param array $ids
     *
     * @return Sheet[]
     */
    public function getSheetsById(array $ids);

    /**
     * @param array  $ids
     * @param string $orderBy
     *
     * @return Sheet[]
     */
    public function getSheetsByIdOrdered(array $ids, string $orderBy): array;

    /**
     * @param Event $event
     * @param array $ids
     *
     * @return Sheet[]
     */
    public function getSheetsByEventAndIds(Event $event, array $ids);

    /**
     * @param Event            $event
     * @param Sheet[]          $sheets
     * @param string|null      $state
     * @param string|null      $type
     * @param User|string|null $user
     *
     * @return Sheet[]
     */
    public function getSheetsMetBySheets(
        Event $event,
        array $sheets,
        $state = null,
        $type = null,
        $user = null
    );

    /**
     * @param Event            $event
     * @param Sheet[]          $sheets
     * @param int              $page
     * @param int              $limit
     * @param string|null      $state
     * @param string|null      $type
     * @param User|string|null $user
     *
     * @return PaginatedResult
     */
    public function getSheetsMetBySheetsPaginated(
        Event $event,
        array $sheets,
        $page,
        $limit,
        $state = null,
        $type = null,
        $user = null
    );

    /**
     * @param array $ids
     *
     * @return Sheet[]
     */
    public function getUnvalidatedSheetsById(array $ids);

    /**
     * @param array $ids
     *
     * @return Sheet[]
     */
    public function getSheetsUnacceptedById(array $ids);

    /**
     * @param array $ids
     *
     * @return Sheet[]
     */
    public function getSheetsNotPendingById(array $ids): array;

    /**
     * @param Event $event
     *
     * @return array
     */
    public function getIdsByEvent(Event $event);

    /**
     * @param array $sheets
     *
     * @return Sheet[]
     */
    public function findSheets(array $sheets);

    /**
     * @param int[]  $sheetIds
     * @param string $locale
     *
     * @return Sheet[] indexed by id
     */
    public function getSheetsByIdsWithTypesAndCategories(array $sheetIds, string $locale): array;

    /**
     * @param array $sheetIds
     *
     * @return Sheet[]
     */
    public function findByIds(array $sheetIds);

    /**
     * @param array $sheets
     *
     * @return Sheet[]
     */
    public function findFullSheets(array $sheets);

    /**
     * @param Event $event
     *
     * @return int
     */
    public function countByEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return Sheet[]
     */
    public function getEnabledSheetsByEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return int
     */
    public function countEnabledSheetsByEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return Sheet[]
     */
    public function findEnabledByEvent(Event $event): array;

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return array
     */
    public function countEnabledSheetsTypeByEvent(Event $event, $locale);

    /**
     * @param User $user
     *
     * @return Sheet[]
     */
    public function getByUser(User $user): array;

    /**
     * @param Group $group
     *
     * @return Sheet[]
     */
    public function getByGroup(Group $group);

    /**
     * @param SheetTemplate $sheetTemplate
     *
     * @return Sheet[]
     */
    public function getBySheetTemplate(SheetTemplate $sheetTemplate);

    /**
     * @param RegistrationTemplate $registrationTemplate
     *
     * @return Sheet[]
     */
    public function getByRegistrationTemplate(RegistrationTemplate $registrationTemplate);

    /**
     * @param Type[] $types
     *
     * @return Sheet[]
     */
    public function getByTypes(array $types);

    /**
     * @param int[] $ids
     */
    public function batchUnAssignBySheetsId(array $ids);

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return bool
     */
    public function hasSheetWithGroupByUserByEvent(User $user, Event $event): bool;

    /**
     * @param User  $user
     * @param Group $group
     *
     * @return bool
     */
    public function hasSheetOutOfGroup(User $user, Group $group): bool;

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return bool
     */
    public function isUserParticipantMultipleSheetsInEvent(User $user, Event $event);

    /**
     * @param Event  $event
     * @param string $title
     *
     * @return Sheet|null
     */
    public function getSheetByEventAndTitle(Event $event, $title);

    /**
     * @param Type[] $types
     * @param string $extraDataName Sheet\ExtraData name
     *
     * @return Sheet[]
     */
    public function getByTypesAndWithoutGivenExtraData(array $types, string $extraDataName): array;

    public function hasSheetBeenDuplicatedByEvent(Sheet $sheet, Event $event): bool;

    /**
     * @return Sheet[]
     */
    public function getSheetsEnabledByEvent(Event $event): array;

    /**
     * @param Event $event
     *
     * @return Sheet[]
     */
    public function getNotLinkedSheets(Event $event): array;

    /**
     * @param Sheet[] $sheets
     *
     * @return Sheet[]
     */
    public function filterWithScheduledMeetings(array $sheets): array;

    /**
     * Get analytics (views and clicks) indexed by userId
     */
    public function getAnalyticsByUser(Event $event): array;
}
