<?php

namespace Proximum\Vimeet\Application\Query\CustomLink;

class CustomLinkListView
{
    public array $customLinkViews;

    /**
     * @param CustomLinkView[] $customLinkViews
     */
    public function __construct(array $customLinkViews)
    {
        $this->customLinkViews = $customLinkViews;
    }
}
