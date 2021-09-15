<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Application\Query\Dashboard\View\DashboardUserAndTypeScanView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\Scan;

interface ScanRepositoryInterface
{
    public function add(Scan $scan): void;

    public function getUserFirstCheckinTodayByEvent(User $user, Event $event, \DateTimeInterface $dateTime): ?Scan;

    public function isUserCheckinTodayByEvent(User $user, Event $event, \DateTimeInterface $dateTime): bool;

    /**
     * @return Scan[] indexed by User id
     */
    public function getScanDateByUsersAndEvent(array $users, Event $event, \DateTimeInterface $dateTime): array;

    public function isUserCheckinByEventAndSlot(User $user, Event $event, MeetingSlot $meetingSlot): bool;

    public function hasScanForUserEventTypeAndObjectId(
        User $user,
        Event $event,
        string $scanType,
        int $objectId
    ): bool;

    public function getScanForUserEventTypeAndObjectId(
        User $user,
        Event $event,
        string $scanType,
        int $objectId
    ): ?Scan;

    /**
     * @param Event              $event
     * @param \DateTimeInterface $dateTime
     *
     * @return DashboardUserAndTypeScanView[]
     */
    public function getUserCheckinByEventAndDay(Event $event, \DateTimeInterface $dateTime): array;

    public function getHappeningParticipantsCount(Event $event): array;
}
