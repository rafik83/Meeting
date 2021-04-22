<?php

namespace Proximum\Vimeet\Application\Command\Event\CustomLink;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class Create implements Command
{
    public Event $event;

    public array $translatedLabels;

    public array $types;

    public string $url;

    public string $iconName;

    public string $iconColor;

    public string $labelColor;

    public string $buttonColor;

    public int $priority;

    public function __construct(Event $event, array $locales)
    {
        $this->event = $event;

        foreach ($locales as $locale) {
            $this->translatedLabels[$locale] = [
                'title' => '',
            ];
        }
    }
}
