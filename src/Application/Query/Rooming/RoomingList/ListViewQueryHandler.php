<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList;

use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\ListDetailView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\ListView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\RoommateView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\SheetView;
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

            $sheetView = new SheetView(
                $userSheetTypeView->sheetId,
                $userSheetTypeView->sheetTitle,
                $userSheetTypeView->typeTitle,
                $userSheetTypeView->spotReference
            );

            if (isset($listDetailViews[$userSheetTypeView->userId])) {
                $listDetailViews[$userSheetTypeView->userId]->addSheetView($sheetView);
            } else {
                $listDetailViews[$userSheetTypeView->userId] = new ListDetailView(
                    $userSheetTypeView->userId,
                    $userSheetTypeView->firstName,
                    $userSheetTypeView->lastName,
                    $userSheetTypeView->arrival,
                    $userSheetTypeView->departure,
                    null,
                    [
                        $sheetView
                    ],
                    $userStayViews
                );
            }
        }

        $roommateViewByUserIdByStayId = [];
        foreach ($userIdsByStayId as $stayId => $usersId) {
            if (\count($usersId) > 1) {
                foreach ($usersId as $userId) {
                    $roommateIds = array_filter(
                        $usersId,
                        function ($otherUserId) use ($userId) {
                            return $otherUserId !== $userId;
                        }
                    );

                    foreach ($roommateIds as $roommateId) {
                        if (isset($listDetailViews[$roommateId])) {
                            $roommateViewByUserIdByStayId[$stayId][$userId] = new RoommateView(
                                $roommateId,
                                $listDetailViews[$roommateId]->firstName,
                                $listDetailViews[$roommateId]->lastName
                            );
                        }
                    }
                }
            }
        }

        foreach ($listDetailViews as $userId => $listDetailView) {
            foreach ($listDetailView->userStayViews as $userStayView) {
                if (isset($roommateViewByUserIdByStayId[$userStayView->stayId][$listDetailView->userId])) {
                    $userStayView->roommateView = $roommateViewByUserIdByStayId[$userStayView->stayId][$listDetailView->userId];
                }
            }
        }

        return new ListView($listDetailViews);
    }
}
