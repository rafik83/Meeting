<?php

namespace Proximum\Vimeet\Application\Command\Partner;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Type;

class Update implements Command
{
    /** @var string */
    public $email;

    /** @var string */
    public $lastname;

    /** @var string */
    public $firstname;

    /** @var Admin */
    public $partner;

    /** @var Type[] */
    public $types;

    public function __construct(Admin $partner)
    {
        $this->partner = $partner;
        $this->email = $partner->getEmail();
        $this->firstname = $partner->getFirstname();
        $this->lastname = $partner->getLastname();
        $this->types = $partner->getAllowedTypes();
    }
}
