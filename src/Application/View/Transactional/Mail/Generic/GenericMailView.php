<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Transactional\Mail\Generic;

class GenericMailView
{
    /** @var string */
    public $key;

    /** @var string */
    public $title;

    public function __construct(
        string $key,
        string $title
    ) {
        $this->key = $key;
        $this->title = $title;
    }
}
