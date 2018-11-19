<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

class DateTime extends EditableObject implements ContentObjectInterface, ExportableObjectInterface
{
    private const DATE_FORMAT = 'd/m/Y';
    private const DATETIME_FORMAT = 'd/m/Y H:i';

    public function setDatetime($date): void
    {
        $this->data['datetime'] = $date instanceof \DateTime ? $date->format(self::DATETIME_FORMAT) : null;
    }

    public function getDatetime(): ?\DateTime
    {
        if (!$this->data['datetime']) {
            return null;
        }

        return \DateTime::createFromFormat(
            self::DATETIME_FORMAT,
            $this->data['datetime'],
            new \DateTimeZone(date_default_timezone_get())
        );
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
                self::DATETIME_FORMAT,
                $this->getOption($date),
                new \DateTimeZone(date_default_timezone_get())
            )
            : null;
    }

    public function getDatepickerFormat(): string
    {
        return $this->displayHours() ? self::DATETIME_FORMAT : self::DATE_FORMAT;
    }
}
