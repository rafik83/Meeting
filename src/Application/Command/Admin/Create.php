<?php

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Application\Components\Security\PasswordGenerator;

class Create implements Command
{
    /** @var string */
    public $email;

    /** @var string */
    public $password;

    /** @var string */
    public $lastname;

    /** @var string */
    public $firstname;

    /** @var string */
    public $role;

    /** @var array */
    public $events;

    /** @var string */
    public $locale;

    public function __construct(string $locale)
    {
        $this->locale = $locale;
        $this->password = PasswordGenerator::generate(12);
    }
}
