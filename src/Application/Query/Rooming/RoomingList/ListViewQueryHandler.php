<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList;

use function count;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\AbstractUserStayView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\ListDetailView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\ListView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\RoommateView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\SheetView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\UserSheetTypeView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\UserStayToAssignView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\UserStayView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Time\DaysHelper;
use Proximum\Vimeet\Domain\Time\MidnightTransformer;
use Proximum\Vimeet\Domain\Time\OverlappedTimeRangeTruncater;
use Proximum\Vimeet\Domain\Time\TimeRangeView;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class ListViewQueryHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var StayRepositoryInterface */
    private $stayRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var OverlappedTimeRangeTruncater */
    private $overlappedTimeRangeTruncater;

    public function __construct(
        UserRepositoryInterface $userRepository,
        StayRepositoryInterface $stayRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        OverlappedTimeRangeTruncater $overlappedTimeRangeTruncater
    ) {
        $this->userRepository = $userRepository;
        $this->stayRepository = $stayRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->overlappedTimeRangeTruncater = $overlappedTimeRangeTruncater;
    }

    public function handle(ListViewQuery $query): ListView
    {
        /** @var ListDetailView[] $listDetailViews */
        $listDetailViews = [];
        $userSheetTypeViews = $this->userRepository->getWithSheetAndTypeByEvent($query->event, $query->locale, $query->types, $query->states);
        $stayViews = $this->stayRepository->getStayViewsByEvent($query->event);
        $comments = $this->extraDataRepository->getExtraDataForEventIdAndNameIndexedByUserId(
            $query->event->getId(),
            Type::ROOMING_COMMENT
        );

        $tastings = $this->extraDataRepository->getExtraDataForEventIdAndNameIndexedByUserId(
            $query->event->getId(),
            Type::ROOMING_TASTING
        );

        $stayViewsByUserId = [];
        $userIdsByStayId = [];

        foreach ($stayViews as $stayView) {
            $userId = $stayView->userId;
            $stayViewsByUserId[$userId][] = new UserStayView(
                $stayView->stayId,
                MidnightTransformer::getDateAtMidnight($stayView->arrival),
                MidnightTransformer::getDateAtMidnight($stayView->departure),
                $stayView->accommodationTitle,
                $stayView->roomType,
                $stayView->roomNumber
            );
            $userIdsByStayId[$stayView->stayId][] = $userId;
        }

        $defaultFirstRoomingDay = $this->getDefaultFirstRoomingDay($query->event);
        $defaultLastRoomingDay = $this->getDefaultLastRoomingDay($query->event);

        foreach ($userSheetTypeViews as $userSheetTypeView) {
            $userId = $userSheetTypeView->userId;
            $userStayViews = $stayViewsByUserId[$userId] ?? [];

            if (!isset($listDetailViews[$userId])) {
                $areDatesFilledByUser = $userSheetTypeView->arrival !== null && $userSheetTypeView->departure !== null;
                $listDetailViews[$userId] = new ListDetailView(
                    $userSheetTypeView->userId,
                    $userSheetTypeView->firstName,
                    $userSheetTypeView->lastName,
                    $userSheetTypeView->arrival ?? $defaultFirstRoomingDay,
                    $userSheetTypeView->departure ?? $defaultLastRoomingDay,
                    $areDatesFilledByUser,
                    $userSheetTypeView->hasArrivalHours,
                    $userSheetTypeView->hasDepartureHours,
                    isset($comments[$userId]) && $comments[$userId] instanceof ExtraData
                        ? $comments[$userId]->getValue()
                        : null
                    ,
                    isset($tastings[$userId]) && $tastings[$userId] instanceof ExtraData
                        ? $tastings[$userId]->getValue()
                        : null
                    ,
                    [],
                    $userStayViews
                );
            }

            $listDetailViews[$userId]->addSheetView($this->getSheetView($userSheetTypeView));
        }

        $this->assignRoommateToUserStayView($userIdsByStayId, $listDetailViews);

        $this->getUserStayViewsToAssign($query->event, $listDetailViews);

        return new ListView($listDetailViews, count($listDetailViews));
    }

    private function getSheetView(UserSheetTypeView $userSheetTypeView): SheetView
    {
        return new SheetView(
            $userSheetTypeView->sheetId,
            $userSheetTypeView->sheetTitle,
            $userSheetTypeView->typeTitle,
            $userSheetTypeView->spotReference,
            $userSheetTypeView->sheetState
        );
    }

    private function getRoommateViewByUserIdByStayId(array &$userIdsByStayId, array &$listDetailViews): array
    {
        $roommateViewByUserIdByStayId = [];

        foreach ($userIdsByStayId as $stayId => $usersId) {
            if (count($usersId) === 1) {
                continue;
            }

            foreach ($usersId as $userId) {
                foreach ($usersId as $otherUserId) {
                    // The roommate is the other user on the same stay
                    if ($otherUserId !== $userId && isset($listDetailViews[$otherUserId])) {
                        $roommateViewByUserIdByStayId[$stayId][$userId] = new RoommateView(
                            $otherUserId,
                            $listDetailViews[$otherUserId]->firstName,
                            $listDetailViews[$otherUserId]->lastName
                        );
                    }
                }
            }
        }

        return $roommateViewByUserIdByStayId;
    }

    private function assignRoommateToUserStayView(array &$userIdsByStayId, array &$listDetailViews): void
    {
        $roommateViewByUserIdByStayId = $this->getRoommateViewByUserIdByStayId($userIdsByStayId, $listDetailViews);

        foreach ($listDetailViews as $userId => $listDetailView) {
            foreach ($listDetailView->userStayViews as $userStayView) {
                if ($userStayView instanceof UserStayToAssignView) {
                    continue;
                }

                if (isset($roommateViewByUserIdByStayId[$userStayView->stayId][$listDetailView->userId])) {
                    $userStayView->roommateView = $roommateViewByUserIdByStayId[$userStayView->stayId][$listDetailView->userId];
                }
            }
        }
    }

    /**
     * @param ListDetailView[] $listDetailViews
     */
    private function getUserStayViewsToAssign(Event $event, array &$listDetailViews): void
    {
        foreach ($listDetailViews as $listDetailView) {
            if (null === $listDetailView->arrivalDate
                || null === $listDetailView->departureDate
            ) {
                continue;
            }

            if (empty($listDetailView->userStayViews)) {
                $listDetailView->userStayViews[] = new UserStayToAssignView(
                    $listDetailView->arrivalDate,
                    $listDetailView->departureDate
                );

                continue;
            }

            $period = new TimeRangeView(
                $this->getMidnightDateNotTimezoned($listDetailView->arrivalDate, $event->getTimeZone()),
                $this->getMidnightDateNotTimezoned($listDetailView->departureDate, $event->getTimeZone())
            );

            $timeRangeViews = $this->overlappedTimeRangeTruncater->truncate($period, $listDetailView->userStayViews);

            foreach ($timeRangeViews as $timeRangeView) {
                $listDetailView->userStayViews[] = new UserStayToAssignView($timeRangeView->begin, $timeRangeView->end);
            }

            usort(
                $listDetailView->userStayViews,
                function (AbstractUserStayView $userStayView, AbstractUserStayView $otherUserStayView) {
                    return $userStayView->getBegin() <=> $otherUserStayView->getBegin();
                }
            );
        }
    }

    private function getMidnightDateNotTimezoned(\DateTimeInterface $dateTime, string $eventTimeZone): \DateTimeInterface
    {
        return new \DateTime(DaysHelper::cloneDateTime($dateTime, $eventTimeZone)->format('Y-m-d 0:0:0.000'));
    }

    private function getDefaultFirstRoomingDay(Event $event): \DateTimeInterface
    {
        $defaultRoomingFirstDay = DaysHelper::cloneDateTime($event->getFirstDay()->getDay());

        return $defaultRoomingFirstDay->sub(new \DateInterval('P1D'));
    }

    private function getDefaultLastRoomingDay(Event $event): \DateTimeInterface
    {
        $defaultRoomingLastDay = DaysHelper::cloneDateTime($event->getLastDay()->getDay());

        return $defaultRoomingLastDay;
    }
}
