<?php

namespace Proximum\Vimeet\Domain\Template\Validator;

use Proximum\Vimeet\Domain\Template\Exception\ObjectValidatorNotExistException;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class ObjectValidatorFactory
{
    /**
     * @param TemplateObject $object
     *
     * @throws ObjectValidatorNotExistException
     *
     * @return ObjectValidatorInterface
     */
    public static function create(TemplateObject $object)
    {
        switch ($object) {
            case $object instanceof TemplateObject\Telephone:
                return new TelephoneValidator();
            case $object instanceof TemplateObject\Country:
                return new CountryValidator();
            case $object instanceof TemplateObject\Nomenclature:
                return new NomenclatureValidator();
            default:
                throw new ObjectValidatorNotExistException();
        }
    }
}
