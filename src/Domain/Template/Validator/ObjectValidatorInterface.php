<?php

namespace Proximum\Vimeet\Domain\Template\Validator;

use Proximum\Vimeet\Domain\Template\Validator\Error\ValidatorError;

interface ObjectValidatorInterface
{
    /**
     * @param mixed $data
     * @param array $options
     *
     * @return ValidatorError
     */
    public function validate($data, array $options = []);
}
