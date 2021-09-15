<?php

namespace Proximum\Vimeet\Application\Command\Type\Badge;

class RemovingWhileAddingLeftImageException extends \Exception
{
    public $message = 'Can\'t adding and removing left imagine at the same time';
}
