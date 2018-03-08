<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Validator\Error;

class EmailError extends ValidatorError
{
    const MESSAGE = 'validators.admin.sheet.participant_import.email.error';

    /**
     * EmailError constructor.
     *
     * @param string $data
     * @param bool   $hasNoError
     */
    public function __construct($data, $hasNoError)
    {
        parent::__construct(self::MESSAGE, $data, $hasNoError);
    }
}
