<?php

namespace Proximum\Vimeet\Domain\Unavailability\Exception;

class UnableToDispatchException extends \Exception
{
    public $indication = 'flash.admin.unavailability.mass.dispatch.fail';
}
