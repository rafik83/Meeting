<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Validator\Error;

class CountryError extends ValidatorError
{
    const MESSAGE = 'admin.sheet.import_participant.error.country';

    /**
     * CountryError constructor.
     *
     * @param string $data
     * @param bool   $error
     */
    public function __construct($data, $error)
    {
        parent::__construct(self::MESSAGE, $data, $error);
    }
}
