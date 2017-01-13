<?php
/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Validator;

class TelephoneValidator implements ObjectValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate($data, array $options = [])
    {
        return preg_match('#^\+(?!(?:\d*-){5,})(?!(?:\d* ){5,})[\d- /.]+$#', $data) !== false;
    }
}
