<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Validator\Error;

class EmailExistError extends ValidatorError
{
    const MESSAGE = 'validators.admin.sheet.participant_import.email.exist.error';

    /**
     * EmailExistError constructor.
     *
     * @param string $data
     * @param bool   $error
     */
    public function __construct($data, $error)
    {
        parent::__construct(self::MESSAGE, $data, $error);
    }
}
