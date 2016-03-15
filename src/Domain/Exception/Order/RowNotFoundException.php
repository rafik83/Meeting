<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Exception\Order;

class RowNotFoundException extends OrderException
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
