<?php

namespace Proximum\Vimeet\Application\View\Sheet\Details\Participant;

abstract class AvailabilityConfirmationView
{
    const TRANS_KEY = 'admin.sheet.details.participant.availability_confirmation.';

    /** @var string */
    public $message = '';

    /** @var string */
    public $indicator = '';
}
