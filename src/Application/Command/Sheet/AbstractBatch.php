<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Command;

abstract class AbstractBatch implements Command
{
    /**
     * @var array
     */
    public $ids;

    /**
     * @return string
     */
    public function getMessage()
    {
        return 'flash.admin.sheet_batch.';
    }
}
