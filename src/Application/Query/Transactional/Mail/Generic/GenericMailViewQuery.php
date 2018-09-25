<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Transactional\Mail\Generic;

use Proximum\Vimeet\Application\Query\Query;

class GenericMailViewQuery implements Query
{
    /** @var string */
    public $locale;

    /** @var string */
    public $key;

    /** @var array */
    public $data;

    public function __construct(string $locale, string $key, array $data)
    {
        $this->locale = $locale;
        $this->key = $key;
        $this->data = $data;
    }
}
