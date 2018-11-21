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
        if (!$this->data['datetime']) {
            return null;
        }

        return \DateTime::createFromFormat(
            self::DATETIME_FORMAT,
            $this->data['datetime'],
            new \DateTimeZone($this->getTimezone())
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
        $this->setDatetime($value);
    }

    public function getExportableContent(array $taggedData = [], ?string $locale = null): ?string
    {
        if (!$this->getContentValue()) {
            return null;
        }

        try {
            return (new \DateTime($this->getContentValue()))
                ->setTimezone(new \DateTimeZone($this->getTimezone()))
                ->format(self::DATETIME_FORMAT);
        } catch (\Exception $exception) {
            return null;
        }
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
                new \DateTimeZone($this->getTimezone())
            )
            : null;
    }

    public function getDatepickerFormat(): string
    {
        return $this->displayHours() ? self::DATETIME_FORMAT : self::DATE_FORMAT;
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
