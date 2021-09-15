<?php

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Application\Command\Command;

class UpdateLastLogin implements Command
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
