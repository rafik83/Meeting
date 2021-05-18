<?php

namespace Proximum\Vimeet\Application\Command\Event\CustomLink;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class Update implements Command
{
    public Event\CustomLink $customLink;

    public array $translatedLabels;

    public array $types;

    public array $localizedUrls;

    public string $iconName;

    public string $iconColor;

    public string $labelColor;

    public string $buttonColor;

    public int $priority;

    public function __construct(Event\CustomLink $customLink, array $locales)
    {
        $this->customLink = $customLink;

        foreach ($locales as $locale) {
            $this->translatedLabels[$locale] = [
                'title' => $customLink->getStaticFormulation()->getTitle($locale),
            ];

            $this->localizedUrls[$locale] = [
                'title' => $customLink->getUrl($locale),
            ];
        }

        $this->types = $customLink->getTypes();
        $this->iconName = $customLink->getIconName();
        $this->iconColor = $customLink->getIconColor();
        $this->labelColor = $customLink->getLabelColor();
        $this->buttonColor = $customLink->getButtonColor();
        $this->priority = $customLink->getPriority();
    }
}
