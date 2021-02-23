<?php

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Application\Command\Sheet\BatchEnableDisableHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;

class PostBatchEnableDisable implements Command
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
     * @var array
     */
    public $ids;

    /**
     * @var string
     *
     * @see BatchEnableDisableHandler::STATE_ENABLE
     * @see BatchEnableDisableHandler::STATE_DISABLE
     */
    public $state;

    /**
     * PostBatchEnableDisable constructor.
     *
     * @param Sheet[] $sheets
     * @param array   $ids
     * @param Admin   $admin
     * @param bool    $state
     */
    public function __construct(array $sheets, array $ids, Admin $admin, $state)
    {
        $this->sheets = $sheets;
        $this->admin  = $admin;
        $this->ids    = $ids;
        $this->state  = $state;
    }
}
