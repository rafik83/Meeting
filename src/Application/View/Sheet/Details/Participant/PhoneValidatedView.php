<?php

namespace Proximum\Vimeet\Application\View\Sheet\Details\Participant;

class PhoneValidatedView extends PhoneValidationStatusView
{
    /** @var string */
    public $message = self::TRANS_KEY . 'validated';

    /** @var string */
    public $indicator = 'primary';
}
