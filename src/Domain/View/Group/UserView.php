<?php

namespace Proximum\Vimeet\Domain\View\Group;

class UserView
{
    /** @var int */
    public $id;

    /** @var string */
    public $email;

    /** @var string */
    public $fullName;

    /**
     * UserView constructor.
     *
     * @param int    $id
     * @param string $email
     * @param string $fullName
     */
    public function __construct($id, $email, $fullName)
    {
        $this->id       = $id;
        $this->email    = $email;
        $this->fullName = $fullName;
    }
}
