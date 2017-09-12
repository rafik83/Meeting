<?php
/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

interface ValidatorInterface
{
    const VALIDATOR_EMAIL_TYPE = 'validator_email_type';
    const VALIDATOR_SPOT_IMPORT_TYPE = 'validator_spot_import_type';

    /**
     * @param mixed $data
     * @param string $constraintType
     *
     * @return mixed
     */
    public function validate($data, $constraintType);
}
