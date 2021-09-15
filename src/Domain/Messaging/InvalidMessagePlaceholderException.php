<?php

namespace Proximum\Vimeet\Domain\Messaging;

use Proximum\Vimeet\Domain\Exception\DomainException;

class InvalidMessagePlaceholderException extends DomainException
{
    public function __construct($placeholder)
    {
        parent::__construct("Invalid placeholder '$placeholder'");
    }
}
