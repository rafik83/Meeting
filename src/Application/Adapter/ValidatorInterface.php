<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

interface ValidatorInterface
{
    public const VALIDATOR_EMAIL_TYPE = 'validator_email_type';

    /**
     * @param mixed  $data
     * @param string $constraintType
     *
     * @return mixed
     */
    public function validate($data, $constraintType = null);
}
