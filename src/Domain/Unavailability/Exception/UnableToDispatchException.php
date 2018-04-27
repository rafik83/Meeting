<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Unavailability\Exception;

class UnableToDispatchException extends \Exception
{
    public $indication = 'flash.admin.unavailability.mass.dispatch.fail';
}
