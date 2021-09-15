<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;

class BatchAssign extends AbstractBatch
{
    /**
     * @var array
     */
    public $ids;

    /**
     * @var Admin|null
     */
    public $admin;

    /**
     * BatchAssign constructor.
     *
     * @param array      $ids
     * @param Admin|null $admin
     */
    public function __construct(array $ids, Admin $admin = null)
    {
        $this->ids   = $ids;
        $this->admin = $admin;
    }

    /**
     * @return bool
     */
    public function unassigned()
    {
        return null === $this->admin;
    }
}
