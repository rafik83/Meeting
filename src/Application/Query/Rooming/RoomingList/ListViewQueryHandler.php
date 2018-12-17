<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList;

use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\ListDetailView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\ListView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\RoommateView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\SheetView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\UserSheetTypeView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\UserStayView;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\View\Rooming\StayView;

class ListViewQueryHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var StayRepositoryInterface */
    private $stayRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        StayRepositoryInterface $stayRepository
    ) {
        $this->userRepository = $userRepository;
        $this->stayRepository = $stayRepository;
    }

    public function handle(ListViewQuery $query): ListView
    {
        /** @var ListDetailView[] $listDetailViews */
        $listDetailViews = [];
        $userSheetTypeViews = $this->userRepository->getWithSheetAndTypeByEvent($query->event, $query->locale);
        $stayViews = $this->stayRepository->getStaysByEvent($query->event);

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
            $userStayViews = $stayViewsByUserId[$userSheetTypeView->userId] ?? [];
            $sheetView = $this->getSheetView($userSheetTypeView);

            if (!isset($listDetailViews[$userSheetTypeView->userId])) {
                $listDetailViews[$userSheetTypeView->userId] = new ListDetailView(
                    $userSheetTypeView->userId,
                    $userSheetTypeView->firstName,
                    $userSheetTypeView->lastName,
                    $userSheetTypeView->arrival,
                    $userSheetTypeView->departure,
                    null,
                    [],
                    $userStayViews
                );
            }

            $listDetailViews[$userSheetTypeView->userId]->addSheetView($sheetView);
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
