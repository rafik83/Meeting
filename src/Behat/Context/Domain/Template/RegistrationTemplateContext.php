<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Template;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\Template\RegistrationTemplateContextProxyInterface;

class RegistrationTemplateContext implements Context
{
    /** @var RegistrationTemplateContextProxyInterface */
    private $registrationTemplateContextProxy;

    public function __construct(RegistrationTemplateContextProxyInterface $registrationTemplateContextProxy)
    {
        $this->registrationTemplateContextProxy = $registrationTemplateContextProxy;
    }

    /**
     * @Given there is a registration template
     */
    public function thereIsARegistrationTemplate()
    {
        $nomenclatures = $this->registrationTemplateContextProxy->getStorage()->get('nomenclatures');

        $this->registrationTemplateContextProxy->getRegistrationTemplateManager()->create(null, $nomenclatures);
    }
}
