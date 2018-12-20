<?php

namespace Proximum\Vimeet\Application\Query\Rooming\Stay;

use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class GetSheetUsersHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param GetSheetUsers $getSheetUsers
     *
     * @return User[]
     */
    public function handle(GetSheetUsers $getSheetUsers): array
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($getSheetUsers->user, $getSheetUsers->event);
        $users = [];

        foreach ($sheets as $sheet) {
            foreach ($sheet->getParticipantsArray() as $participant) {
                $user = $participant->getUser();

                if (!isset($users[$user->getId()]) && $user->getId() !== $getSheetUsers->user->getId()) {
                    $users[$user->getId()] = $user;
                }
            }
        }

        return $users;
    }
}
