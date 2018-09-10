<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Converter;

class GenderConverter implements ConverterInterface
{
    public static function convert(string $data): string
    {
        return 'M' === $data ? 'man' : 'woman';
    }
}
