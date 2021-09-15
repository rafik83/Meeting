<?php

namespace Proximum\Vimeet\Application\Query\Badge\QRCode;

use Proximum\Vimeet\Application\Query\Query;

class QRCodeIdentifierToUserQuery implements Query
{
    /** @var string */
    public $identifier;

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }
}
