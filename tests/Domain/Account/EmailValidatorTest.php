<?php

namespace Proximum\Vimeet\Tests\Domain\Account;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\ValidatorInterface;
use Proximum\Vimeet\Domain\Account\EmailValidator;

class EmailValidatorTest extends TestCase
{
    private $validator;

    public function setUp()
    {
        $this->validator = $this->prophesize(ValidatorInterface::class);
    }

    public function testValidateNullData()
    {
        // Data
        $data = null;

        // Mock
        $this->validator->validate($data, ValidatorInterface::VALIDATOR_EMAIL_TYPE)->shouldNotBeCalled();

        // Service
        $emailValidator = new EmailValidator($this->validator->reveal());
        $result = $emailValidator->validate($data);

        $this->assertFalse($result);
    }

    public function testValidateWithViolation()
    {
        // Data
        $data = '';

        // Mock
        $this->validator->validate($data, ValidatorInterface::VALIDATOR_EMAIL_TYPE)->shouldBeCalled()->willReturn([
            'violation1',
            'violation2',
        ]);

        // Service
        $emailValidator = new EmailValidator($this->validator->reveal());
        $result = $emailValidator->validate($data);

        $this->assertFalse($result);
    }

    public function testValidate()
    {
        // Data
        $data = '';

        // Mock
        $this->validator->validate($data, ValidatorInterface::VALIDATOR_EMAIL_TYPE)->shouldBeCalled()->willReturn([]);

        // Service
        $emailValidator = new EmailValidator($this->validator->reveal());
        $result = $emailValidator->validate($data);

        $this->assertTrue($result);
    }
}
