<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\VideoConference;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class RequestAccess
{
    /** @var Meeting */
    public $meeting;

    /** @var Participant */
    public $participant;

    /** @var User */
    public $user;

    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    public function __construct(
        Meeting $meeting,
        Participant $participant,
        string $locale
    ) {
        $this->meeting = $meeting;
        $this->participant = $participant;
        $this->locale = $locale;

        $this->user = $participant->getUser();
        $this->sheet = $participant->getSheet();
    }
}
