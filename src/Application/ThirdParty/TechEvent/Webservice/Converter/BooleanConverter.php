<?php

namespace Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Converter;

class BooleanConverter implements ConverterInterface
{
    public static function convert(string $data): bool
    {
        if (!$data) {
            return false;
        }

        return (bool)$data;
    }
}
