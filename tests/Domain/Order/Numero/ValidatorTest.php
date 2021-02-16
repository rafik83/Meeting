<?php

namespace Proximum\Vimeet\Tests\Domain\Order\Numero;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Order\Numero\Validator;

class ValidatorTest extends TestCase
{
    public function testIsValid()
    {
        // The numero should be composed of 3 int seperate by two hyphens

        // Check with letters
        $this->assertEquals(false, Validator::isValid('test'));
        $this->assertEquals(false, Validator::isValid('a-a'));
        $this->assertEquals(false, Validator::isValid('a-a-a'));
        $this->assertEquals(false, Validator::isValid('a-a-a-a'));
        $this->assertEquals(false, Validator::isValid('a&a/a~a'));
        $this->assertEquals(false, Validator::isValid('a~a'));

        // Check with numbers, letters and wrong number of hypens
        $this->assertEquals(false, Validator::isValid('1-1-1-1'));
        $this->assertEquals(false, Validator::isValid('a-100-100'));
        $this->assertEquals(false, Validator::isValid('100-a-100'));
        $this->assertEquals(false, Validator::isValid('100-100-a'));
        $this->assertEquals(false, Validator::isValid('02-EE-02'));

        // Check with int, float
        $this->assertEquals(false, Validator::isValid(1));
        $this->assertEquals(false, Validator::isValid(2000000));
        $this->assertEquals(false, Validator::isValid(2.2));
        $class = new \DateTime();
        $this->assertEquals(false, Validator::isValid($class));

        // Check that correct numero are valid
        $this->assertEquals(true, Validator::isValid('1-1-1'));
        $this->assertEquals(true, Validator::isValid('02-02-02'));
        $this->assertEquals(true, Validator::isValid('100-100-100'));
    }
}
