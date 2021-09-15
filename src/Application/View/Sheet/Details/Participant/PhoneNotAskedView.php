<?php

namespace Proximum\Vimeet\Application\View\Sheet\Details\Participant;

class PhoneNotAskedView extends PhoneValidationStatusView
{
    /**
     * Not asked are considered as not validated
     *
     * @var string
     */
    public $message = self::TRANS_KEY . 'not_validated';

    /** @var string */
    public $indicator = 'danger';
}
