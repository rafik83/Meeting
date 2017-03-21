<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Invoice;

use Proximum\Vimeet\Domain\Model\Admin;

class InvoiceExportQuery
{
    /**
     * @var Admin
     */
    public $user;

    /**
     * @var \DateTimeInterface
     */
    public $beginDate;

    /**
     * @var \DateTimeInterface
     */
    public $endDate;

    /**
     * InvoiceExportQuery constructor.
     *
     * @param Admin $user
     */
    public function __construct(Admin $user)
    {
        $this->user = $user;
    }
}
