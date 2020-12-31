<?php

namespace Proximum\Vimeet\Domain\Account;

use Proximum\Vimeet\Application\Adapter\ValidatorInterface;

class EmailValidator
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * EmailValidator constructor.
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param string $data
     *
     * @return bool
     */
    public function validate($data)
    {
        if (null === $data) {
            return false;
        }

        $violation = $this->validator->validate($data, ValidatorInterface::VALIDATOR_EMAIL_TYPE);

        return 0 === count($violation);
    }
}
