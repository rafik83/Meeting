<?php

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;

class PostBatchValidate implements Command
{
    /**
     * @var Sheet[]
     */
    public $sheets;

    /**
     * @var Admin
     */
    public $admin;

    /**
     * @var string
     */
    public $comment;

    /**
     * PostBatchValidate constructor.
     *
     * @param Sheet[] $sheets
     * @param Admin   $admin
     * @param string  $comment
     */
    public function __construct(array $sheets, Admin $admin, $comment)
    {
        $this->sheets  = $sheets;
        $this->admin   = $admin;
        $this->comment = $comment;
    }
}
