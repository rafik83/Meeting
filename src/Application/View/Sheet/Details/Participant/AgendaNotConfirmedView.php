<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Details\Participant;

class AgendaNotConfirmedView extends AgendaConfirmationStatusView
{
    /** @var string */
    public $message = self::TRANS_KEY . 'agenda_not_confirmed';

    /** @var string */
    public $indicator = 'danger';
}
