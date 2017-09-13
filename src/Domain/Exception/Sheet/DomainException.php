<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Exception\Sheet;

use Proximum\Vimeet\Domain\Exception\DomainException;

class SheetDomainException extends DomainException
{
    /** @var string */
    public $message;

    /** @var int */
    public $code;

    /**
     * @param string $message
     * @param int    $code
     */
    public function __construct(string $message = 'Access Denied', int $code = 403)
    {
        $this->message = $message;
        $this->code = $code;
    }
}
