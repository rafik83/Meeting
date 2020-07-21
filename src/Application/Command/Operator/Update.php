<?php

namespace Proximum\Vimeet\Application\Command\Operator;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class Update implements Command
{
    /** @var string */
    public $email;

    /** @var string */
    public $lastname;

    /** @var string */
    public $firstname;

    /** @var Admin */
    public $operator;

    /** @var Event[] */
    public $events;

    /** @var Event[] */
    public $allowedEventsByAdmin;

    /**
     * @param Admin   $operator
     * @param Event[] $allowedEventsByAdmin
     */
    public function __construct(Admin $operator, array $allowedEventsByAdmin)
    {
        $this->operator = $operator;
        $this->email = $operator->getEmail();
        $this->lastname = $operator->getLastname();
        $this->firstname = $operator->getFirstname();
        $this->events = $operator->getEvents()->toArray();
        $this->allowedEventsByAdmin = $allowedEventsByAdmin;
    }
}
