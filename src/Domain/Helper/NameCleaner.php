<?php

namespace Proximum\Vimeet\Domain\Helper;

class NameCleaner
{
    public static function cleanFirstName(?string $name): string
    {
        if (null === $name) {
            return '';
        }

        return ucwords(mb_strtolower($name), ' -');
    }

    public static function cleanLastName(?string $name): string
    {
        if (null === $name) {
            return '';
        }

        return mb_strtoupper($name);
    }
}
