<?php

namespace Proximum\Vimeet\Application\Query\CustomLink;

use Proximum\Vimeet\Domain\Intention\IntentionType;

class CustomLinkView
{
    public int $id;
    public string $label;
    /** @var string[]  */
    public array $urls;
    public array $typeTitles;
    public int $priority;

    /**
     * @param string[] $typeTitles
     * @param string[] $urls
     */
    public function __construct(int $id, string $label, array $urls, array $typeTitles, int $priority)
    {
        $this->id = $id;
        $this->label = $label;
        $this->urls = $urls;
        $this->typeTitles = $typeTitles;
        $this->priority = $priority;
    }

    public function getIntention(): string
    {
        return IntentionType::INTENTION_REMOVE_CUSTOM_LINK;
    }
}
