<?php

namespace Proximum\Vimeet\Domain\Helper;

class StringHelper
{
    public static function trimSpacesAndNonBreakSpaces(string $str): string
    {
        return trim($str, " \t\n\r\0\x0b\xc2\xa0");
    }
}
