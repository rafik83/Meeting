<?php

namespace Proximum\Vimeet\Application\Components\Security;

final class PasswordGenerator
{
    private const CHARS = 'abcdefghijklmnopqrstuvwxyz';
    private const CHARS_UPPERCASE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const NUMBERS = '0123456789';
    private const SPECIAL_CHARS = '!@#$%^&*()_-=+;:,.?';

    public static function generate(int $length = 10): string
    {
        if ($length < 3) {
            throw new \InvalidArgumentException('A generated password length can not be less than 3 chars');
        }

        // The password must contain at least a char, an uppercased char, and a number.
        $charIndex = random_int(0, 25);
        $upperIndex = random_int(0, 25);
        $numberIndex = random_int(0, 9);

        $password = self::CHARS[$charIndex] . self::CHARS_UPPERCASE[$upperIndex] . self::NUMBERS[$numberIndex];
        $string = self::CHARS . self::CHARS_UPPERCASE . self::NUMBERS . self::SPECIAL_CHARS;

        for ($i = 4; $i <= $length; $i++) {
            $randomCharIndex = random_int(0, 80);
            $password .= $string[$randomCharIndex];
        }

        return str_shuffle($password);
    }
}
