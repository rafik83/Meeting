<?php

namespace Proximum\Vimeet\Application\Command\Partner;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Application\Components\Security\PasswordGenerator;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Type;

class Create implements Command
{
    /** @var string */
    public $email;

    /** @var string */
    public $lastname;

    /** @var string */
    public $firstname;

    /** @var string */
    public $password;

    /** @var Admin */
    public $organizer;

    /** @var Type[] */
    public $types;

    public function __construct(Admin $organizer)
    {
        $this->organizer = $organizer;
        $this->password = PasswordGenerator::generate(12);
    }
}
