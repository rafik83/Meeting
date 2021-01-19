<?php

namespace Proximum\Vimeet\Application\View\Sheet\Details\Participant;

class AvailabilityConfirmedView extends AvailabilityConfirmationView
{
    /** @var string */
    public $message = self::TRANS_KEY . 'confirmed';

    /** @var string */
    public $indicator = 'success';
}
