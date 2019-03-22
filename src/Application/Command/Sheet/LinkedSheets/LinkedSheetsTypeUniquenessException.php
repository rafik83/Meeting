<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\LinkedSheets;

class LinkedSheetsTypeUniquenessException extends \Exception
{
    protected $message = 'All linked sheets have to be of the same type.';
}
