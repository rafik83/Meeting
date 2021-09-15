<?php

namespace Proximum\Vimeet\Application\Command\Template\Registration;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;

class Duplicate implements Command
{
    /** @var RegistrationTemplate */
    public $registrationTemplate;

    /** @var string */
    public $title;

    /** @var Event */
    public $event;

    public function __construct(RegistrationTemplate $registrationTemplate)
    {
        $this->registrationTemplate = $registrationTemplate;
        $this->event = $registrationTemplate->getEvent();
    }
}
