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
}
