<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\LinkedSheets;

class NotEnoughSheetsException extends \Exception
{
    protected $message = 'Linked sheets have to be gathered at least by 2.';
}
