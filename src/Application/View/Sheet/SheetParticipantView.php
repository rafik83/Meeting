<?php

namespace Proximum\Vimeet\Application\View\Sheet;

class SheetParticipantView
{
    /** @var string */
    public $firstname;

    /** @var string */
    public $lastname;

    /** @var string */
    public $email;

    /** @var int */
    public $id;

    public function __construct(string $firstname, string $lastname, string $email, int $id)
    {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->id = $id;
    }
}
