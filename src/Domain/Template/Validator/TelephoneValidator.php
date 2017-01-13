<?php
/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
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
        $validation = preg_match('#^\+(?!(?:\d*-){5,})(?!(?:\d* ){5,})[\d- /.]+$#', $data) !== false;

        return new TelephoneError($data, $validation);
    }
}
