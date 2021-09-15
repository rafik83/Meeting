<?php

namespace Proximum\Vimeet\Application\Exception\Group;

class UserNotAllowedToManageGroupException extends GroupException
{
    /** @var string */
    public $email;

    /**
     * UserNotAllowedToManageGroupException constructor.
     *
     * @param string $email
     */
    public function __construct($email)
    {
        parent::__construct();

        $this->email = $email;
    }
}
