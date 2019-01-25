<?php

namespace Proximum\Vimeet\Application\Query\Rooming\Stay;

use Proximum\Vimeet\Domain\Model\User;

class GetRoommatesHandler
{
    /**
     * @var GetSheetUsersHandler
     */
    private $getSheetUsersHandler;

    public function __construct(GetSheetUsersHandler $getSheetUsersHandler)
    {
        $this->getSheetUsersHandler = $getSheetUsersHandler;
    }

    /**
     * @param GetRoommates $getRoommates
     *
     * @return User[]
     */
    public function handle(GetRoommates $getRoommates)
    {
        if ($getRoommates->sheet) {
            return $getRoommates->sheet->getUsers();
        }

        return $this->getSheetUsersHandler->handle(new GetSheetUsers($getRoommates->user, $getRoommates->event));
    }
}
