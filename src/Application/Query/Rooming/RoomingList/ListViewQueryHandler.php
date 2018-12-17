<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList;

use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\ListDetailView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\ListView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\RoommateView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\SheetView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\UserOvernightAccommodationView;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class ListViewQueryHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(ListViewQuery $query): ListView
    {
        $results = [];
        $users = $this->userRepository->getWithSheetAndTypeByEvent($query->event, $query->locale);

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
                    null,
                    null,
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
