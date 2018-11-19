<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

class DateTime extends EditableObject implements ContentObjectInterface, ExportableObjectInterface
{
    private const INTERNATIONAL_FORMAT = 'd/m/Y H:i';

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

    public function getDatetimeMin(): ?\DateTime
    {
        return $this->getOption('datetime_min')
            ? \DateTime::createFromFormat(self::INTERNATIONAL_FORMAT, $this->getOption('datetime_min'))
            : null;
    }

    public function getDatetimeMax(): ?\DateTime
    {
        return $this->getOption('datetime_max')
            ? \DateTime::createFromFormat(self::INTERNATIONAL_FORMAT, $this->getOption('datetime_max'))
            : null;
    }
}
