<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Normalizer;

use Proximum\Vimeet\Domain\Model\Admin;

class InvoicesNormalizerView
{
    /**
     * @var Admin
     */
    public $user;

    /**
     * InvoicesNormalizerView constructor.
     *
     * @param Admin $user
     */
    public function __construct(Admin $user)
    {
        $this->user = $user;
    }
}
