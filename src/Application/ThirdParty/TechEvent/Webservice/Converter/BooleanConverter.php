<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Converter;

class BooleanConverter implements ConverterInterface
{
    public static function convert(string $data): bool
    {
        return true;
    }
}
