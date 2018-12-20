<?php

namespace Proximum\Vimeet\Application\Query\Rooming\Stay;

use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class GetSheetUsersHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    public function handle(GetSheetUsers $getSheetUsers)
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($getSheetUsers->user, $getSheetUsers->event);
        $users = [];

        foreach ($sheets as $sheet) {
            foreach ($sheet->getParticipantsArray() as $participant) {
                $user = $participant->getUser();

                if (!isset($users[$user->getId()]) && $user !== $getSheetUsers->user) {
                    $users[$user->getId()] = $user;
                }
            }
        }

        return $users;
    }
}
