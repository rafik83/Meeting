<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Messaging;

use Proximum\Vimeet\Domain\Exception\DomainException;

class InvalidMessagePlaceholderException extends DomainException
{
    public function __construct($placeholder)
    {
        parent::__construct("Invalid placeholder '$placeholder'");
    }
}
