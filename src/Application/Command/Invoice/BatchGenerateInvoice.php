<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Invoice;

use Proximum\Vimeet\Domain\Model\Admin;

class BatchGenerateInvoice
{
    /**
     * @var int[] of Sheet id
     */
    public $sheetIds;

    /**
     * @var Admin
     */
    public $admin;

    /**
     * @param array $sheetIds
     * @param Admin $admin
     */
    public function __construct(array $sheetIds, Admin $admin)
    {
        $this->sheetIds = $sheetIds;
        $this->admin    = $admin;
    }
}
