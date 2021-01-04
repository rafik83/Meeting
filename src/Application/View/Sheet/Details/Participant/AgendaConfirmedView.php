<?php

namespace Proximum\Vimeet\Application\View\Sheet\Details\Participant;

class AgendaConfirmedView extends AgendaConfirmationStatusView
{
    /** @var string */
    public $message = self::TRANS_KEY . 'agenda_confirmed';

    /** @var string */
    public $indicator = 'success';
}
