<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Details\Participant;

class AvailabilityConfirmedView extends AvailabilityConfirmationView
{
    /** @var string */
    public $message = self::TRANS_KEY . 'confirmed';

    /** @var string */
    public $indicator = 'success';
}
