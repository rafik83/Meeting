<?php

namespace Proximum\Vimeet\Application\Query\Rooming\Stay;

use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class GetRoommatesHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param GetRoommates $getRoommates
     *
     * @return User[]
     */
    public function handle(GetRoommates $getRoommates): array
    {
        if ($getRoommates->sheet) {
            $sheets = [$getRoommates->sheet];
        } else {
            $sheets = $this->sheetRepository->getSheetsByUserAndEvent($getRoommates->user, $getRoommates->event);
        }
        $users = [];

        foreach ($sheets as $sheet) {
            foreach ($sheet->getParticipantsArray() as $participant) {
                $user = $participant->getUser();

                if (!isset($users[$user->getId()]) && $user->getId() !== $getRoommates->user->getId()) {
                    $users[$user->getId()] = $user;
                }
            }
        }

        return $users;
    }
}
