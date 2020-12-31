<?php

namespace Proximum\Vimeet\Domain\User\Event;

use Proximum\Vimeet\Application\View\User\Event\AuthenticationTokenImportView;

class AuthenticationTokenImport
{
    /** @var AuthenticationTokenImportView */
    public $authenticationTokenImportView;

    /** @var array */
    public $errors;

    public function __construct(?AuthenticationTokenImportView $authenticationTokenImportView = null)
    {
        $this->errors = [];
        $this->authenticationTokenImportView = $authenticationTokenImportView;
    }

    public function hasError(): bool
    {
        return \count($this->errors) > 0;
    }

    public function addError(string $error): self
    {
        $this->errors[] = $error;

        return $this;
    }
}
