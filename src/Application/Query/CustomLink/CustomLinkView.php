<?php

namespace Proximum\Vimeet\Application\Query\CustomLink;

use Proximum\Vimeet\Domain\Intention\IntentionType;

class CustomLinkView
{
    public int $id;
    public string $label;
    public string $url;
    public array $typeTitles;
    public int $priority;

    /**
     * @param string[] $typeTitles
     */
    public function __construct(int $id, string $label, string $url, array $typeTitles, int $priority)
    {
        $this->id = $id;
        $this->label = $label;
        $this->url = $url;
        $this->typeTitles = $typeTitles;
        $this->priority = $priority;
    }

    public function getIntention(): string
    {
        return IntentionType::INTENTION_REMOVE_CUSTOM_LINK;
    }
}
