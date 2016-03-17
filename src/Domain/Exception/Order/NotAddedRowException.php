<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Exception\Order;

class NotAddedRowException extends OrderException
{
    /**
     * NotAddedRowException constructor.
     *
     * @param string $group
     * @param string $row
     */
    public function __construct($group, $row)
    {
        parent::__construct(sprintf('Row "%s.%s" is not an added_row type', $group, $row));
    }
}
