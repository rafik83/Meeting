<?php

namespace Proximum\Vimeet\Domain\Model\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\Type;

class CustomLink
{
    private int $id;

    private Event $event;

    private StaticFormulation $staticFormulation;

    /** @var LocalizedCustomLinkUrl[]|ArrayCollection */
    private $localizedUrls;

    private string $iconName;

    private string $iconColor;

    private string $labelColor;

    private string $buttonColor;

    private int $priority;

    public function __construct(
        Event $event,
        StaticFormulation $staticFormulation,
        string $iconName,
        string $iconColor,
        string $labelColor,
        string $buttonColor,
        int $priority
    ) {
        $this->event = $event;
        $this->staticFormulation = $staticFormulation;
        $this->localizedUrls = new ArrayCollection();
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

    public function getUrl($locale): string
    {
        if ($this->hasUrl($locale)) {
            return $this->localizedUrls->get($locale)->getUrl();
        }

        return '';
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

    public function hasUrl(string $locale): bool
    {
        return $this->localizedUrls->containsKey($locale);
    }

    public function update(
        array $translations,
        array $types,
        array $localizedUrls,
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

        $this->iconName = $iconName;
        $this->iconColor = $iconColor;
        $this->labelColor = $labelColor;
        $this->buttonColor = $buttonColor;
        $this->priority = $priority;

        foreach ($localizedUrls as $locale => $localizedUrl) {
            $this->setLocalizedUrl($locale, $localizedUrl);
        }
    }

    public function setLocalizedUrl(string $locale, string $url): void
    {
        if ($this->localizedUrls->containsKey($locale)) {
            $this->localizedUrls->get($locale)->update($url);
        } else {
            $this->localizedUrls->set($locale, new LocalizedCustomLinkUrl($this, $locale, $url));
        }
    }

    /**
     * @return LocalizedCustomLinkUrl[]
     */
    public function getLocalizedUrls(): array
    {
        return $this->localizedUrls->toArray();
    }
}
