<?php

namespace Proximum\Vimeet\Application\Command\Type\Badge;

class NoRightImageToRemoveException extends \Exception
{
    public $message = 'No right image to remove';
}
