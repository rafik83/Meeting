<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class Delete
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var User
     */
    public $requester;

    /**
     * @var integer
     */
    public $participantId;

    /**
     * @param Sheet   $sheet
     * @param User    $requester
     * @param integer $participantId
     */
    public function __construct(Sheet $sheet, User $requester, $participantId)
    {
        $this->sheet         = $sheet;
        $this->requester     = $requester;
        $this->participantId = $participantId;
    }
}
