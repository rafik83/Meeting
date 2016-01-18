<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Proforma;

class BillingView
{
    /**
     * @var array
     */
    public $template;

    /**
     * @var array
     */
    public $data;

    /**
     * BillingView constructor.
     *
     * @param array $template
     * @param array $data
     */
    public function __construct(array $template, array $data)
    {
        $this->template = $template;
        $this->data     = $data;
    }
}
