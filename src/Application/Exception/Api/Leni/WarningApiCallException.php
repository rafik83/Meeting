<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Exception\Api\Leni;

class WarningApiCallException extends LeniException
{
    /** @var array */
    public $warnings;

    /**
     * @param array $warnings
     */
    public function __construct(array $warnings)
    {
        parent::__construct();

        $this->warnings = $warnings;
    }
}
