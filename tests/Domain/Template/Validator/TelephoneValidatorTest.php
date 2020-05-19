<?php

namespace Proximum\Vimeet\Tests\Domain\Template\Validator;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Template\Validator\Error\TelephoneError;
use Proximum\Vimeet\Domain\Template\Validator\TelephoneValidator;

class TelephoneValidatorTest extends TestCase
{
    public function testValidate(): void
    {
        $tel = [
            '123456789', // missing +
            '+123456789',
            '+azertyuiop', // no number
            '+123-123-123-123', // hyphen ok
            '+123-azertyuiop-123', // not number char
            '+555 123 123 123', // space ok
            '+123-123-123-@-123', // special char
            '+(123) 123/123.123', // parenthesis not ok
            '+1233', // short ok
            '+123 123/123.123-123', // space slash dot and hyphen ok
        ];

        $validator = new TelephoneValidator();

        $this->assertEquals(
            new TelephoneError($tel[0], false),
            $validator->validate($tel[0])
        );
        $this->assertEquals(
            new TelephoneError($tel[1], true),
            $validator->validate($tel[1])
        );
        $this->assertEquals(
            new TelephoneError($tel[2], false),
            $validator->validate($tel[2])
        );
        $this->assertEquals(
            new TelephoneError($tel[3], true),
            $validator->validate($tel[3])
        );
        $this->assertEquals(
            new TelephoneError($tel[4], false),
            $validator->validate($tel[4])
        );
        $this->assertEquals(
            new TelephoneError($tel[5], true),
            $validator->validate($tel[5])
        );
        $this->assertEquals(
            new TelephoneError($tel[6], false),
            $validator->validate($tel[6])
        );
        $this->assertEquals(
            new TelephoneError($tel[7], false),
            $validator->validate($tel[7])
        );
        $this->assertEquals(
            new TelephoneError($tel[8], true),
            $validator->validate($tel[8])
        );
        $this->assertEquals(
            new TelephoneError($tel[9], true),
            $validator->validate($tel[9])
        );
    }
}
