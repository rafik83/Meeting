<?php

namespace Proximum\Vimeet\Application\Query\Badge\ScannedUserEventProfile;

class ScannedUserEventProfileView
{
    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    public function __construct(string $firstName, string $lastName)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }
}
