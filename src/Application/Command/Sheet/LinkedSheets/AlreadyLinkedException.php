<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\LinkedSheets;

class AlreadyLinkedException extends \Exception
{
    protected $message = 'Sheet is already linked to other sheets.';
}
