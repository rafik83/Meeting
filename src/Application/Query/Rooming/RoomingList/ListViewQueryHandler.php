<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList;

use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\ListDetailView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\ListView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\RoommateView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\SheetView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\UserOvernightAccommodationView;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

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
        $results = [];
        $users = $this->userRepository->getWithSheetAndTypeByEvent($query->event, $query->locale);
        $stays = $this->stayRepository->getStaysByEvent($query->event);

        foreach ($users as $user) {
            $sheetView = new SheetView(
                $user->sheetId,
                $user->sheetTitle,
                $user->typeTitle,
                $user->spotReference
            );

            if (isset($results[$user->userId])) {
                $results[$user->userId]->addSheetView($sheetView);
            } else {
                $results[$user->userId] = new ListDetailView(
                    $user->userId,
                    $user->firstName,
                    $user->lastName,
                    $user->arrival,
                    $user->departure,
                    null,
                    [
                        $sheetView
                    ],
                    []
                );
            }

        }

        return new ListView($results);
    }
}
