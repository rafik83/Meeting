<?php

namespace Proximum\Vimeet\Application\Command\Sheet\LinkedSheets;

class HasScheduledMeetingException extends \Exception
{
    protected $message = 'Sheet with scheduled meetings can\'t be linked.';
}
