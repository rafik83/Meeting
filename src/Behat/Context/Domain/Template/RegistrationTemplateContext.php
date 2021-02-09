<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Template;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\Template\RegistrationTemplateContextProxyInterface;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;

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
     * this template has 2 goals:
     * - provide a way to test multi-steps registration
     * - provide a template with file upload fields (can't be on first step)
     *
     * If a type is created before, it will create a template if none exist, run this context first to be sure that
     * only one template is created
     */
    public function thereIsARegistrationTemplate()
    {
        $event = $this->registrationTemplateContextProxy->getStorage()->get('event');
        $nomenclatures = $this->registrationTemplateContextProxy->getStorage()->get('nomenclatures');

        $template = $this->registrationTemplateContextProxy->getRegistrationTemplateManager()->create2stepsTemplate($event, $nomenclatures);
        $this->registrationTemplateContextProxy->getStorage()->set('template', $template);
    }

    /**
     * @Given there is a third step on this template
     */
    public function thereIsAThirdStepOnThisTemplate()
    {
        /** @var RegistrationTemplate */
        $template = $this->registrationTemplateContextProxy->getStorage()->get('template');

        $this->registrationTemplateContextProxy->getRegistrationTemplateManager()->addStepToTemplate($template);
        $this->registrationTemplateContextProxy->getStorage()->set('template', $template);
    }

    /**
     * @Given there is a single step registration template
     */
    public function thereIsASingleStepRegistrationTemplate()
    {
        $event = $this->registrationTemplateContextProxy->getStorage()->get('event');
        $nomenclatures = $this->registrationTemplateContextProxy->getStorage()->get('nomenclatures');

        $template = $this->registrationTemplateContextProxy->getRegistrationTemplateManager()->create1stepTemplate($event, $nomenclatures);
        $this->registrationTemplateContextProxy->getStorage()->set('template', $template);
    }
}
