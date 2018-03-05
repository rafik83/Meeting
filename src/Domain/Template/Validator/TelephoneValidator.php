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
        if (empty($data) || $data === null) {
            return new TelephoneError($data, true);
        }

        $validation = preg_match('#^\+(?!(?:\d*-){5,})(?!(?:\d* ){5,})[\d- /.]+$#', $data) !== 0;

        return new TelephoneError($data, $validation);
    }
}
