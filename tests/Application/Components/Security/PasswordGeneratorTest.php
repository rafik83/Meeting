<?php

namespace Proximum\Vimeet\Tests\Application\Components\Security;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Security\PasswordGenerator;

class PasswordGeneratorTest extends TestCase
{
    public function testGenerate(): void
    {
        $password1 = PasswordGenerator::generate(10);
        $password2 = PasswordGenerator::generate(20);

        $this->assertEquals(10, strlen($password1));
        $this->assertEquals(20, strlen($password2));

        // At least a number and a char
        $regex = '^(?=.*[A-Za-z])(?=.*\d)^';
        $this->assertRegExp($regex, $password1);
        $this->assertRegExp($regex, $password2);
    }
}
