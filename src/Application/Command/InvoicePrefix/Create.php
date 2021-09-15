<?php

namespace Proximum\Vimeet\Application\Command\InvoicePrefix;

use Proximum\Vimeet\Application\Command\Command;

class Create implements Command
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $prefix;
}
