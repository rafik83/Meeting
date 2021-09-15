<?php

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
