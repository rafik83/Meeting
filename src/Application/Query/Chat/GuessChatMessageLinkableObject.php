<?php

namespace Proximum\Vimeet\Application\Query\Chat;

use Proximum\Vimeet\Application\Query\Query;

class GuessChatMessageLinkableObject implements Query
{
    /** @var string */
    public $objectType;

    /** @var int */
    public $objectId;

    public function __construct(string $objectType, int $objectId)
    {
        $this->objectType = $objectType;
        $this->objectId = $objectId;
    }
}
