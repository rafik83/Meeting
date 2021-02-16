<?php

namespace Proximum\Vimeet\Application\View\Sheet\Details\Participant;

class AvailabilityNotConfirmedView extends AvailabilityConfirmationView
{
    /** @var string */
    public $message = self::TRANS_KEY . 'not_confirmed';

    /** @var string */
    public $indicator = 'danger';
}
