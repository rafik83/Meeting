<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Invoice;

use Proximum\Vimeet\Domain\Model\Invoice\Prefix;

interface PrefixRepositoryInterface
{
    /**
     * @param Prefix $prefix
     */
    public function add(Prefix $prefix);

    /**
     * @return Prefix[]
     */
    public function getAll();

    /**
     * @return Prefix|null
     */
    public function getDefault();
}
