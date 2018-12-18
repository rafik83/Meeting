<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList;

use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\ListDetailView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\ListView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\RoommateView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\SheetView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\UserSheetTypeView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\UserStayView;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class ListViewQueryHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var StayRepositoryInterface */
    private $stayRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        StayRepositoryInterface $stayRepository,
        ExtraDataRepositoryInterface $extraDataRepository
    ) {
        $this->userRepository = $userRepository;
        $this->stayRepository = $stayRepository;
        $this->extraDataRepository = $extraDataRepository;
    }

    public function handle(ListViewQuery $query): ListView
    {
        /** @var ListDetailView[] $listDetailViews */
        $listDetailViews = [];
        $userSheetTypeViews = $this->userRepository->getWithSheetAndTypeByEvent($query->event, $query->locale);
        $stayViews = $this->stayRepository->getStaysByEvent($query->event);
        $comments = $this->extraDataRepository->getExtraDataForEventIdAndNameIndexedByUserId(
            $query->event->getId(),
            Type::ROOMING_COMMENT
        );

        $stayViewsByUserId = [];
        $userIdsByStayId = [];

        foreach ($stayViews as $stayView) {
            $stayViewsByUserId[$stayView->userId][] = new UserStayView(
                $stayView->stayId,
                $stayView->arrival,
                $stayView->departure,
                $stayView->accommodationTitle,
                $stayView->roomType
            );

            $userIdsByStayId[$stayView->stayId][] = $stayView->userId;
        }

        foreach ($userSheetTypeViews as $userSheetTypeView) {
            $userId = $userSheetTypeView->userId;
            $userStayViews = $stayViewsByUserId[$userId] ?? [];

            if (!isset($listDetailViews[$userId])) {
                $listDetailViews[$userId] = new ListDetailView(
                    $userSheetTypeView->userId,
                    $userSheetTypeView->firstName,
                    $userSheetTypeView->lastName,
                    $userSheetTypeView->arrival,
                    $userSheetTypeView->departure,
                    $userSheetTypeView->hasArrivalHours,
                    $userSheetTypeView->hasDepartureHours,
                    isset($comments[$userId]) && $comments[$userId] instanceof ExtraData
                        ? $comments[$userId]->getValue()
                        : null
                    ,
                    [],
                    $userStayViews
                );
            }

            $listDetailViews[$userId]->addSheetView($this->getSheetView($userSheetTypeView));
        }

        $this->assignRoommateToUserStayView($userIdsByStayId, $listDetailViews);

        return new ListView($listDetailViews);
    }

    private function getSheetView(UserSheetTypeView $userSheetTypeView): SheetView
    {
        return new SheetView(
            $userSheetTypeView->sheetId,
            $userSheetTypeView->sheetTitle,
            $userSheetTypeView->typeTitle,
            $userSheetTypeView->spotReference
        );
    }

    private function getRoommateViewByUserIdByStayId(array &$userIdsByStayId, array &$listDetailViews): array
    {
        $roommateViewByUserIdByStayId = [];

        foreach ($userIdsByStayId as $stayId => $usersId) {
            if (\count($usersId) === 1) {
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
                if (isset($roommateViewByUserIdByStayId[$userStayView->stayId][$listDetailView->userId])) {
                    $userStayView->roommateView = $roommateViewByUserIdByStayId[$userStayView->stayId][$listDetailView->userId];
                }
            }
        }
    }
}
