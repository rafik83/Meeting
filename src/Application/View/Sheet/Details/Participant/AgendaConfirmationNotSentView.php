<?php

namespace Proximum\Vimeet\Application\View\Sheet\Details\Participant;

class AgendaConfirmationNotSentView extends AgendaConfirmationStatusView
{
    /** @var string */
    public $message = self::TRANS_KEY . 'agenda_confirmation_not_sent';

    /** @var string */
    public $indicator = 'warning';
}
