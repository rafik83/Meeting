<?php

namespace Proximum\Vimeet\Application\Command\Type\Badge;

class RemovingWhileAddingRightImageException extends \Exception
{
    public $message = 'Can\'t adding and removing right imagine at the same time';
}
