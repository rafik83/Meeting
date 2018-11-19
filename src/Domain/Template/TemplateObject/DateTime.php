<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

class DateTime extends EditableObject implements ContentObjectInterface, ExportableObjectInterface
{
    private const INTERNATIONAL_FORMAT = 'd/m/Y H:i';

    public function setDatetime($date): void
    {
        $this->data['datetime'] = $date instanceof \DateTime ? $date->format('Y-m-d H:i:s') : null;
    }

    public function getDatetime(): ?\DateTime
    {
        return new \DateTime($this->data['datetime']);
    }

    public function getContentValue(): ?string
    {
        return $this->data['datetime'] ?? null;
    }

    public function getContentValueLocalize($locale = null): ?string
    {
        return $this->getContentValue();
    }

    public function getContentLabel(): ?string
    {
        return $this->getContentValue();
    }

    public function setContentValue($value): void
    {
        $this->setDate($value);
    }

    public function getExportableContent(array $taggedData = [], ?string $locale = null): ?string
    {
        if (!$this->getContentValue()) {
            return null;
        }

        return (new \DateTime($this->getContentValue()))
            ->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    }

    public function getExportableFieldname($locale, $fallback): ?string
    {
        return $this->getLabel($locale);
    }

    public function displayHours(): bool
    {
        return 'datetime' === $this->getOption('format');
    }

    public function getOptionDate(string $date): ?\DateTime
    {
        return $this->getOption($date)
            ? \DateTime::createFromFormat(
                self::INTERNATIONAL_FORMAT,
                $this->getOption($date),
                new \DateTimeZone(date_default_timezone_get())
            )
            : null;
    }
}
