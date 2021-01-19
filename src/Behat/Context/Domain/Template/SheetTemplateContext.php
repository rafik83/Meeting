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

    /**
     * @Given child :objectId of sheet templates has product options
     */
    public function childOfThisSheetTemplateHasProductOptions($objectId)
    {
        $event = $this->sheetTemplateContextProxy->getStorage()->get('event');
        $options = $this->sheetTemplateContextProxy->getStorage()->get('options');
        if (null === $options) {
            throw new \InvalidArgumentException('Missing options');
        }

        $this->sheetTemplateContextProxy->getSheetTemplateManager()->updateChild(
            $event,
            $objectId,
            'products',
            array_map(fn ($option) => $option->getId(), $options)
        );
    }
}
