<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

class CheckboxObject extends EditableObject implements ContentObjectInterface, ExportableObjectInterface
{
    public function getContentValue()
    {
        return $this->data['bool'] ?? null;
    }

    public function getContentValueLocalize($locale = null)
    {
        return $this->getContentValue();
    }

    public function getContentLabel()
    {
        return $this->getContentValue();
    }

    public function setContentValue($value)
    {
        $this->data['bool'] = $value;
    }

    public function getExportableContent(array $taggedData = [], ?string $locale = null)
    {
        if ($this->getContentValue() === null) {
            return null;
        }

        return $this->getContentValue() ? '1' : '0';
    }

    public function getExportableFieldname($locale, $fallback)
    {
        return $this->getLabel($locale, $fallback);
    }
}
