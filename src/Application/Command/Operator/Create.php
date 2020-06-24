<?php

namespace Proximum\Vimeet\Application\Command\Operator;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

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

    /** @var Admin */
    public $organizer;

    /** @var Event[] */
    public $events;

    public function __construct(Admin $organizer)
    {
        $this->organizer = $organizer;
        $this->password = substr(md5(uniqid()), 0, 8);
    }
}
