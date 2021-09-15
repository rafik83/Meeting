<?php

namespace Proximum\Vimeet\Application\Exception\Group;

class UserNotFoundForGivenEmailException extends GroupException
{
    /** @var string */
    public $email;

    /**
     * UserNotFoundForGivenEmailException constructor.
     *
     * @param string $email
     */
    public function __construct($email)
    {
        parent::__construct();

        $this->email = $email;
    }
}
