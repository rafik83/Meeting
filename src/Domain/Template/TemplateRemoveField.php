<?php

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;

class TemplateRemoveField
{
    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * TemplateRemoveField constructor.
     *
     * @param TemplateDataFactory $templateDataFactory
     */
    public function __construct(TemplateDataFactory $templateDataFactory)
    {
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @param SheetTemplate $template
     * @param string        $fieldName
     * @param mixed         $withEmptyValue
     *
     * @return array
     */
    public function remove(SheetTemplate $template, $fieldName, $withEmptyValue)
    {
        $templateData = $this->templateDataFactory->createFromTemplate($template);

        $templateData->removeField($fieldName, $withEmptyValue);

        return $templateData->normalize();
    }
}
