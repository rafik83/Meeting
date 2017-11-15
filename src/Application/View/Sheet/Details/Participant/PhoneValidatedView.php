<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Details\Participant;

class PhoneValidatedView extends PhoneValidationStatusView
{
    /** @var string */
    public $message = self::TRANS_KEY . 'validated';

    /** @var string */
    public $indicator = 'primary';
}
