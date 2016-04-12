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
     * @var string
     */
    public $locale;

    /**
     * @param DateTimeInterface $createdAt
     * @param string            $locale
     */
    public function __construct(DateTimeInterface $createdAt, $locale)
    {
        $this->createdAt = $createdAt;
        $this->locale    = $locale;
    }
}
