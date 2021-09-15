<?php

namespace Proximum\Vimeet\Application\Command\Type\Badge;

class NoRightImageToSetFullHeightException extends \Exception
{
    public $message = 'Right image is mandatory to set full height';
}
