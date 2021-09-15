<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\ValidatorInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidatorInterface;

class ValidatorAdapter implements ValidatorInterface
{
    /**
     * @var SymfonyValidatorInterface
     */
    private $validator;

    /**
     * ValidatorAdapter constructor.
     *
     * @param SymfonyValidatorInterface $validator
     */
    public function __construct(SymfonyValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param mixed       $data
     * @param null|string $constraintType
     *
     * @return mixed
     */
    public function validate($data, $constraintType = null)
    {
        switch ($constraintType) {
            case self::VALIDATOR_EMAIL_TYPE:
                return $this->validator->validate($data, [new NotBlank(), new Email(['strict' => true])]);
            default:
                return $this->validator->validate($data);
        }
    }
}
