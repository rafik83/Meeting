<?php

namespace Proximum\Vimeet\Application\View\Sheet\Details\Participant;

class PhoneNotValidatedView extends PhoneValidationStatusView
{
    /** @var string */
    public $message = self::TRANS_KEY . 'not_validated';

    /** @var string */
    public $indicator = 'danger';
}
