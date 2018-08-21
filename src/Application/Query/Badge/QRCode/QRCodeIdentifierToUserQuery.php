<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Badge\QRCode;

use Proximum\Vimeet\Application\Query\Query;

class QRCodeIdentifierToUserQuery implements Query
{
    /** @var string */
    public $identifier;

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }
}
