<?php

namespace Proximum\Vimeet\Domain\Template\Validator;

use Proximum\Vimeet\Domain\Template\Validator\Error\CountryError;
use Symfony\Component\Intl\Intl;

class CountryValidator implements ObjectValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate($data, array $options = [])
    {
        $countries = Intl::getRegionBundle()->getCountryNames($options['locale']);

        return new CountryError($data, array_key_exists($data, $countries));
    }
}
