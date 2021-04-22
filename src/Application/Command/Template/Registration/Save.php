<?php

namespace Proximum\Vimeet\Application\Command\Template\Registration;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;

class Save implements Command
{
    /** @var RegistrationTemplate */
    public $registrationTemplate;

    /** @var array */
    public $value;

    /**
     * @param RegistrationTemplate $registrationTemplate
     * @param array                $value
     */
    public function __construct(RegistrationTemplate $registrationTemplate, array $value)
    {
        $this->registrationTemplate = $registrationTemplate;
        $this->value = $value;
    }
}
