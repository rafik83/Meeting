<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User\Agenda\Version;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class Notify extends AbstractVersionCommand
{
    /** @var Sheet */
    public $sheet;

    /**
     * @param Event $event
     * @param Sheet $sheet
     * @param User  $user
     */
    public function __construct(Event $event, Sheet $sheet, User $user)
    {
        parent::__construct($event, $user);

        $this->sheet = $sheet;
    }
}
