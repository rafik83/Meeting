<?php

namespace Proximum\Vimeet\Application\Exception\Group;

class UserAlreadyGroupManagerOnSameEventException extends GroupException
{
    /** @var string */
    public $email;

    /**
     * UserNotAllowedToManageGroupException constructor.
     *
     * @param string|null $email
     */
    public function __construct($email = null)
    {
        parent::__construct();

        $this->email = $email;
    }
}
