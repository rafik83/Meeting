<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Billing;

use Proximum\Vimeet\Domain\Model\Sheet;

class Update
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var array
     */
    public $billingData = [];

    /**
     * @param Sheet $sheet
     * @param array $billingData
     */
    public function __construct(Sheet $sheet, array $billingData)
    {
        $this->sheet = $sheet;
        $this->billingData = $billingData;
    }
}
