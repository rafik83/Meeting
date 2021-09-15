<?php

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;

class PostBatchRefuse implements Command
{
    /** @var Sheet[] */
    public $sheets;

    /** @var Admin */
    public $admin;

    public function __construct(array $sheets, Admin $admin)
    {
        $this->sheets = $sheets;
        $this->admin = $admin;
    }
}
