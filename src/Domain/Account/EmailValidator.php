<?php
/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Account;

use Proximum\Vimeet\Application\Adapter\ValidatorInterface;

class EmailValidator
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * EmailValidator constructor.
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param string $data
     *
     * @return mixed
     */
    public function validate($data)
    {
        if ($data === null) {
            return false;
        }

        $violation = $this->validator->validate($data, ValidatorInterface::VALIDATOR_EMAIL_TYPE);

        return count($violation) === 0;
    }
}
