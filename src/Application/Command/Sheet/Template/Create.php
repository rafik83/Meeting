<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use DateTimeInterface;

class Create
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var DateTimeInterface
     */
    public $createdAt;

    /**
     * @param DateTimeInterface $createdAt
     */
    public function __construct(DateTimeInterface $createdAt)
    {
        $this->createdAt = $createdAt;
    }
}
