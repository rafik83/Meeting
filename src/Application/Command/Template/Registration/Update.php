<?php

namespace Proximum\Vimeet\Application\Command\Template\Registration;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;

class Update implements Command
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var array
     */
    public $value;

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
        $this->title                = $registrationTemplate->getTitle();
        $this->value                = $registrationTemplate->getValue();
    }
}
