<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PurchasingFunnel;

use Proximum\Vimeet\Domain\Model\PurchasingFunnel;

class CreateResult
{
    /**
     * @var PurchasingFunnel
     */
    public $purchasingFunnel;

    /**
     * CreateResult constructor.
     *
     * @param PurchasingFunnel $purchasingFunnel
     */
    public function __construct(PurchasingFunnel $purchasingFunnel)
    {
        $this->purchasingFunnel = $purchasingFunnel;
    }
}
