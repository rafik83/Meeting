<?php

namespace Proximum\Vimeet\Application\Command\Group\Participant;

use Proximum\Vimeet\Application\View\Group\Participant\UserParticipantView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;

class UpdateUsersSheets
{
    /** @var Group */
    public $group;

    /** @var array of User id => Sheet[] */
    public $sheetsByUser = [];

    /**
     * @param Group                 $group
     * @param UserParticipantView[] $userParticipantViews
     */
    public function __construct(Group $group, array $userParticipantViews)
    {
        $this->group = $group;

        foreach ($userParticipantViews as $userParticipantView) {
            $this->sheetsByUser[$userParticipantView->userId] = $userParticipantView->sheets;
        }
    }

    /**
     * @param int $userId
     *
     * @return Sheet[]
     */
    public function __get($userId)
    {
        return $this->sheetsByUser[$userId];
    }

    /**
     * @param int     $userId
     * @param Sheet[] $sheets
     */
    public function __set($userId, $sheets)
    {
        $this->sheetsByUser[$userId] = $sheets;
    }
}
