<?php

namespace Proximum\Vimeet\Application\View\Sheet\Details\Participant;

class AgendaNotConfirmedView extends AgendaConfirmationStatusView
{
    /** @var string */
    public $message = self::TRANS_KEY . 'agenda_not_confirmed';

    /** @var string */
    public $indicator = 'danger';
}
