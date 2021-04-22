<?php

namespace Proximum\Vimeet\Domain\Model\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\Type;

class CustomLink
{
    private int $id;

    private Event $event;

    private StaticFormulation $staticFormulation;

    private string $url;

    private string $iconName;

    private string $iconColor;

    private string $labelColor;

    private string $buttonColor;

    private int $priority;

    public function __construct(
        Event $event,
        StaticFormulation $staticFormulation,
        string $url,
        string $iconName,
        string $iconColor,
        string $labelColor,
        string $buttonColor,
        int $priority
    ) {
        $this->event = $event;
        $this->staticFormulation = $staticFormulation;
        $this->url = $url;
        $this->iconName = $iconName;
        $this->iconColor = $iconColor;
        $this->labelColor = $labelColor;
        $this->buttonColor = $buttonColor;
        $this->priority = $priority;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLabel($locale): string
    {
        return $this->staticFormulation->getTitle($locale);
    }

    /**
     * @return Type[]
     */
    public function getTypes(): array
    {
        return $this->staticFormulation->getTypes();
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getStaticFormulation(): StaticFormulation
    {
        return $this->staticFormulation;
    }

    public function getIconName(): string
    {
        return $this->iconName;
    }

    public function getIconColor(): string
    {
        return $this->iconColor;
    }

    public function getLabelColor(): string
    {
        return $this->labelColor;
    }

    public function getButtonColor(): string
    {
        return $this->buttonColor;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function update(
        array $translations,
        array $types,
        string $url,
        string $iconName,
        string $iconColor,
        string $labelColor,
        string $buttonColor,
        int $priority
    ): void {
        foreach ($translations as $locale => $translation) {
            $this->staticFormulation->translate($locale, $translation['title']);
        }
        $this->staticFormulation->update($types);

        $this->url = $url;
        $this->iconName = $iconName;
        $this->iconColor = $iconColor;
        $this->labelColor = $labelColor;
        $this->buttonColor = $buttonColor;
        $this->priority = $priority;
    }
}
