<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Validator;

use Proximum\Vimeet\Domain\Template\Validator\Error\TelephoneError;

class TelephoneValidator implements ObjectValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate($data, array $options = [])
    {
        if (empty($data) || null === $data) {
            return new TelephoneError($data, true);
        }

        $validation = 0 !== preg_match('#^\+(?!(?:\d*-){5,})(?!(?:\d* ){5,})[\d\- /.]+$#', $data);

        return new TelephoneError($data, $validation);
    }
}
