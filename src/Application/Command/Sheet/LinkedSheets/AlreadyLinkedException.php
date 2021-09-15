<?php

namespace Proximum\Vimeet\Application\Command\Sheet\LinkedSheets;

class AlreadyLinkedException extends \Exception
{
    protected $message = 'Sheet is already linked to other sheets.';
}
