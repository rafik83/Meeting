<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Proximum\Vimeet\Domain\Model\Event;

class DateTime extends EditableObject implements ContentObjectInterface, ExportableObjectInterface, ContextEventInterface
{
    private const DATE_FORMAT = 'd/m/Y';
    private const DATETIME_FORMAT = 'd/m/Y H:i';

    /** @var Event */
    private $event;

    public function setDatetime($date): void
    {
        $this->data['datetime'] = $date instanceof \DateTime ? $date->format(self::DATETIME_FORMAT) : null;
    }

    public function getDatetime(): ?\DateTime
    {
        if (empty($this->data['datetime'])) {
            return null;
        }

        return \DateTime::createFromFormat(self::DATETIME_FORMAT, $this->data['datetime']);
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
        $this->setDatetime($value);
    }

    public function getExportableContent(array $taggedData = [], ?string $locale = null): ?string
    {
        if (!$this->getContentValue()) {
            return null;
        }

        $date = \DateTime::createFromFormat(self::DATETIME_FORMAT, $this->getContentValue());

        return $date ? $date->format($this->getDatepickerFormat()) : null;
    }

    public function getExportableFieldname($locale, $fallback): ?string
    {
        return $this->getLabel($locale);
    }

    public function displayHours(): bool
    {
        return 'datetime' === $this->getOption('format');
    }

    public function getOptionDate(string $optionDate): ?\DateTime
    {
        $option = $this->getOption($optionDate);
        if (empty($option)) {
            return null;
        }

        $date = \DateTime::createFromFormat(self::DATETIME_FORMAT, $option);

        return false === $date ? null : $date;
    }

    public function getDatepickerFormat(): string
    {
        return $this->displayHours() ? self::DATETIME_FORMAT : self::DATE_FORMAT;
    }

    public function getOptionDateFormattedForDatepicker(string $date): ?string
    {
        $dateTime = $this->getOptionDate($date);

        if (!$dateTime) {
            return null;
        }

        return $dateTime->format('Y-m-d H:i');
    }

    public function getTimezone(): string
    {
        return $this->getEvent() ? $this->getEvent()->getTimeZone() : date_default_timezone_get();
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    public function getFormattedDate(string $locale): ?string
    {
        if (!$this->getDatetime()) {
            return null;
        }

        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::SHORT,
            $this->displayHours() ? \IntlDateFormatter::SHORT : \IntlDateFormatter::NONE,
            $this->getTimezone()
        );

        return $formatter->format($this->getDatetime());
    }
}
