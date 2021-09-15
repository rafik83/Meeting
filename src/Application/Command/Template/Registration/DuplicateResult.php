<?php

namespace Proximum\Vimeet\Application\Command\Template\Registration;

use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;

class DuplicateResult
{
    /** @var RegistrationTemplate */
    public $registrationTemplate;

    public function __construct(RegistrationTemplate $registrationTemplate)
    {
        $this->registrationTemplate = $registrationTemplate;
    }
}
