<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query;

class GetCustomData
{
    /** @var array */
    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
