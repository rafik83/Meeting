<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

class DateTime extends EditableObject implements ContentObjectInterface
{
    public function setDate($date): void
    {
        $this->data['date'] = $date;
    }

    public function getDate(): ?string
    {
        return $this->data['date'] ?? null;
    }

    public function getContentValue(): ?string
    {
        return $this->getDate() ?? null;
    }

    public function getContentValueLocalize($locale = null): ?string
    {
        return $this->getContentValue();
    }

    public function getContentLabel(): string
    {
        return $this->getContentValue();
    }

    public function setContentValue($value): void
    {
        $this->setDate($value);
    }
}
