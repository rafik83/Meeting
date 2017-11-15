<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Details\Participant;

class PhoneNotAskedView extends PhoneValidationStatusView
{
    /** @var string */
    public $message = self::TRANS_KEY . 'not_asked';

    /** @var string */
    public $indicator = 'warning';
}
