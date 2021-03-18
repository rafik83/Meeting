<?php

namespace Proximum\Vimeet\Application\Command\Sheet\LinkedSheets;

class LinkedSheetsTypeUniquenessException extends \Exception
{
    protected $message = 'All linked sheets have to be of the same type.';
}
