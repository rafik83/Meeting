<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Group\Participant;

use Proximum\Vimeet\Application\View\Group\Participant\UserParticipantView;
use Proximum\Vimeet\Domain\Model\Sheet;

class UpdateUsersSheets
{
    /**
     * @var array of User id => Sheet[]
     */
    public $sheetsByUser = [];

    /**
     * @param UserParticipantView[] $userParticipantViews
     */
    public function __construct(array $userParticipantViews)
    {
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
     * @param int $userId
     * @param Sheet[] $sheets
     */
    public function __set($userId, $sheets)
    {
        $this->sheetsByUser[$userId] = $sheets;
    }
}
