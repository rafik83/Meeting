<?php

namespace Proximum\Vimeet\Application\Query\CustomLink;

class CustomLinkView
{
    public string $label;
    public string $url;
    public array $typeTitles;

    /**
     * @param string   $label
     * @param string   $url
     * @param string[] $typeTitles
     */
    public function __construct(string $label, string $url, array $typeTitles)
    {
        $this->label = $label;
        $this->url = $url;
        $this->typeTitles = $typeTitles;
    }
}
