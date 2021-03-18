<?php

namespace Proximum\Vimeet\Application\Exception\Import;

class InvalidEmailException extends \Exception
{
    private string $invalidEmail;

    public function __construct(string $invalidEmail, string $message = '') {
        parent::__construct($message);
        $this->invalidEmail = $invalidEmail;
    }

    public function getInvalidEmail(): string
    {
        return $this->invalidEmail;
    }
}
