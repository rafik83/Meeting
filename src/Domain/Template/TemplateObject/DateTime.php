<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

class DateTime extends EditableObject implements ContentObjectInterface
{
    public function getContentValue(): ?string
    {
        return '';
    }

    public function getContentValueLocalize($locale = null): ?string
    {
        return $this->getContentValue();
    }

    public function getContentLabel(): string
    {
        return '';
    }

    public function setContentValue($value): void
    {
    }
}
