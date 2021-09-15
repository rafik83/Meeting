<?php

namespace Proximum\Vimeet\Application\View\Sheet\Details\Participant;

abstract class PhoneValidationStatusView
{
    const TRANS_KEY = 'admin.sheet.details.participant.phoneValidationStatus.';

    /** @var string */
    public $message = '';

    /** @var string */
    public $indicator = 'warning';
}
