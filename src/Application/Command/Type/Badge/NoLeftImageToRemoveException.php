<?php

namespace Proximum\Vimeet\Application\Command\Type\Badge;

class NoLeftImageToRemoveException extends \Exception
{
    public $message = 'No left image to remove';
}
