<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Factory;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class ParticipantFactory
{
    /**
     * @param Sheet     $sheet
     * @param User|null $user
     *
     * @return Participant
     */
    public static function create(Sheet $sheet, User $user = null)
    {
        $user = ($user !== null) ? $user : new User('user@vimeet.com', 'salt', 'password', 'fr');

        return new Participant($sheet, $user, [], true);
    }
}
