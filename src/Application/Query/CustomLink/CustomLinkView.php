<?php

namespace Proximum\Vimeet\Application\Query\CustomLink;

use Proximum\Vimeet\Domain\Intention\IntentionType;

class CustomLinkView
{
    public int $id;
    public string $label;
    public string $url;
    public array $typeTitles;

    /**
     * @param int      $id
     * @param string   $label
     * @param string   $url
     * @param string[] $typeTitles
     */
    public function __construct(int $id, string $label, string $url, array $typeTitles)
    {
        $this->id = $id;
        $this->label = $label;
        $this->url = $url;
        $this->typeTitles = $typeTitles;
    }

    public function getIntention(): string
    {
        return IntentionType::INTENTION_REMOVE_CUSTOM_LINK;
    }
}
