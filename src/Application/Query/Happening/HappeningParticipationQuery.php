<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\View\Happening\ProgramView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class HappeningParticipationQuery
{
    /**
     * @var ProgramView
     */
    public $programView;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var User
     */
    public $currentUser;

    /**
     * @param ProgramView $programView
     * @param Sheet       $sheet
     * @param User        $currentUser
     */
    public function __construct(ProgramView $programView, Sheet $sheet, User $currentUser)
    {
        $this->programView = $programView;
        $this->sheet       = $sheet;
        $this->currentUser = $currentUser;
    }
}
