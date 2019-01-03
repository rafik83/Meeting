<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList\View;

class RoommateView
{
    /** @var int */
    public $id;

    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    public function __construct(
        int $id,
        string $firstName,
        string $lastName
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }
}
