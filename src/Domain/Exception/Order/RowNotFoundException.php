<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Exception\Order;

use Proximum\Vimeet\Domain\Model\Exception\DomainException;

class RowNotFoundException extends DomainException
{
    /**
     * NotAddedRowException constructor.
     *
     * @param string $group
     * @param string $row
     */
    public function __construct($group, $row)
    {
        parent::__construct(sprintf('Row "%s.%s" not found', $group, $row));
    }
}
