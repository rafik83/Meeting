<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\LinkedSheets\Admin;

class LinkedSheetsView
{
    /** @var string[] */
    public $titles;

    /** @var \DateTimeInterface */
    public $createdAt;

    public function __construct(array $titles, \DateTimeInterface $createdAt)
    {
        $this->titles = $titles;
        $this->createdAt = $createdAt;
    }
}
