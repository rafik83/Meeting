<?php

namespace Proximum\Vimeet\Domain\Template\Validator;

use Proximum\Vimeet\Domain\Template\TemplateObject\Gender;
use Proximum\Vimeet\Domain\Template\Validator\Error\GenderError;

class GenderValidator implements ObjectValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate($data, array $options = [])
    {
        return new GenderError($data, \in_array($data, Gender::getGenders(), true));
    }
}
