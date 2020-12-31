<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Template;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\Template\SheetTemplateContextProxyInterface;

class SheetTemplateContext implements Context
{
    /** @var SheetTemplateContextProxyInterface */
    private $sheetTemplateContextProxy;

    /**
     * @param SheetTemplateContextProxyInterface $sheetTemplateContextProxy
     */
    public function __construct(SheetTemplateContextProxyInterface $sheetTemplateContextProxy)
    {
        $this->sheetTemplateContextProxy = $sheetTemplateContextProxy;
    }

    /**
     * @Given there is a sheet template
     */
    public function thereIsASheetTemplate()
    {
        $event = $this->sheetTemplateContextProxy->getStorage()->get('event');
        $this->sheetTemplateContextProxy->getSheetTemplateManager()->create($event, null);
    }
}
