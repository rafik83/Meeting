<?php

namespace Proximum\Vimeet\Application\Command\Event\Participant;

class TypeMissingForFastCheckinException extends \Exception
{
    public $message = 'Type is mandatory to checkin';
}
