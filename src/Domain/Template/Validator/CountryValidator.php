<?php
/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Validator;

use Symfony\Component\Intl\Intl;

class CountryValidator implements ObjectValidatorInterface
{

    /**
     * {@inheritdoc}
     */
    public function validate($data, array $options = [])
    {
        $countries = Intl::getRegionBundle()->getCountryNames($options['locale']);

        return array_key_exists($data, $countries);
    }
}
