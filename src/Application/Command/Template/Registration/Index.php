<?php

namespace Proximum\Vimeet\Application\Command\Template\Registration;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;

class Index implements Command
{
    /**
     * @var RegistrationTemplate
     */
    public $registrationTemplate;

    /**
     * @param RegistrationTemplate $registrationTemplate
     */
    public function __construct(RegistrationTemplate $registrationTemplate)
    {
        $this->registrationTemplate = $registrationTemplate;
    }
}
