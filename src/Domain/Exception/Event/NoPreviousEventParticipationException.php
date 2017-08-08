<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Exception\Event;

class NoPreviousEventParticipationException extends \Exception
{
    protected $message = 'No previous event participation exception';
}
