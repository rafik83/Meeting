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
use Proximum\Vimeet\Domain\Model\User;

class RequestAccess
{
    /** @var Meeting */
    public $meeting;

    /** @var User */
    public $user;

    /** @var string */
    public $locale;

    public function __construct(
        Meeting $meeting,
        User $user,
        string $locale
    ) {
        $this->meeting = $meeting;
        $this->user    = $user;
        $this->locale = $locale;
    }
}
