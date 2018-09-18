<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Converter;

class TelephoneConverter
{
    public static function convert(string $data, string $countryCode): string
    {
        if ('FR' !== $countryCode) {
            return $data;
        }

        return sprintf('+33%s', substr($data, 1));
    }
}
