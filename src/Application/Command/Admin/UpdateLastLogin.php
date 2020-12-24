<?php

namespace Proximum\Vimeet\Application\Command\Admin;

class UpdateLastLogin
{
    /**
     * @var string
     */
    public $email;

    /**
     * @param string $email
     */
    public function __construct($email)
    {
        $this->email = $email;
    }
}
