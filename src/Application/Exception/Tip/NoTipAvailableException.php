<?php

namespace Proximum\Vimeet\Application\Exception\Tip;

class NoTipAvailableException extends TipException
{
    /** @var string */
    protected $message = 'flash.admin.tip.no_tip_available';
}
