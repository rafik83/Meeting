<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Exception\Speaker;

use Throwable;

class EmailDoesNotExistException extends \Exception
{
    public function __construct($message = "Email does not exists", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
