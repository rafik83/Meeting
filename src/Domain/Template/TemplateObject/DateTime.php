<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

class DateTime extends EditableObject implements ContentObjectInterface, ExportableObjectInterface
{
    public function setDate($date): void
    {
        $this->data['date'] = $date;
    }

    public function getDate(): ?\DateTime
    {
        return $this->data['date'] ?? null;
    }

    public function getContentValue(): ?\DateTime
    {
        return $this->getDate() ?? null;
    }

    public function getContentValueLocalize($locale = null): ?\DateTime
    {
        return $this->getContentValue();
    }

    public function getContentLabel(): ?\DateTime
    {
        return $this->getContentValue();
    }

    public function setContentValue($value): void
    {
        $this->setDate($value);
    }

    public function getExportableContent(array $taggedData = [], ?string $locale = null): ?string
    {
        return $this->getLabel($locale);
    }

    public function getExportableFieldname($locale, $fallback): ?string
    {
        return $this->getContentValue() ? $this->getContentValue()->format('d/m/Y H:i') : null;
    }

    public function displayHours(): bool
    {
        return 'datetime' === $this->getOption('format');
    }
}
