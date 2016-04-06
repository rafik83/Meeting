<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Domain\Model\Event;

class OrganizerCreate
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var Event
     */
    public $event;
}
