<?php

namespace Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Converter;

class GenderConverter implements ConverterInterface
{
    public static function convert(string $data): string
    {
        return 'M' === trim($data) ? 'man' : 'woman';
    }
}
