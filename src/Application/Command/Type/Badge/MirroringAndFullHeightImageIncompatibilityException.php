<?php

namespace Proximum\Vimeet\Application\Command\Type\Badge;

class MirroringAndFullHeightImageIncompatibilityException extends \Exception
{
    public $message = 'Mirror badge and full height image can\'t be set together';
}
