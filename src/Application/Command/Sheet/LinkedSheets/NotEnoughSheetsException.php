<?php

namespace Proximum\Vimeet\Application\Command\Sheet\LinkedSheets;

class NotEnoughSheetsException extends \Exception
{
    protected $message = 'Linked sheets have to be gathered at least by 2.';
}
