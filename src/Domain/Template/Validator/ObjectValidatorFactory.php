<?php
/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Validator;


use Proximum\Vimeet\Domain\Template\Exception\ObjectValidatorNotExistException;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class ObjectValidatorFactory
{
    /**
     * @param TemplateObject $object
     *
     * @return ObjectValidatorInterface
     *
     * @throws ObjectValidatorNotExistException
     */
    public static function create(TemplateObject $object)
    {
        switch ($object) {
            case $object instanceof TemplateObject\Telephone:
                return new TelephoneValidator();
                break;
            case $object instanceof TemplateObject\Country:
                return new CountryValidator();
                break;
            default:
                throw new ObjectValidatorNotExistException();
        }
    }
}
