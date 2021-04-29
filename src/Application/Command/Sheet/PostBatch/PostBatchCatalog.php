<?php

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Application\Command\Sheet\BatchCatalogHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;

class PostBatchCatalog implements Command
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
     *
     * @see BatchCatalogHandler::ADD_CATALOG
     * @see BatchCatalogHandler::REMOVE_CATALOG
     */
    public $state;

    /**
     * PostBatchCatalogHandler constructor.
     *
     * @param Sheet[] $sheets
     * @param Admin   $admin
     * @param string  $state
     */
    public function __construct(array $sheets, Admin $admin, $state)
    {
        $this->sheets = $sheets;
        $this->admin  = $admin;
        $this->state  = $state;
    }
}
