<?php

namespace Proximum\Vimeet\Application\View\CatalogVisibility;

class MessageView
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $content;

    /**
     * MessageView constructor.
     *
     * @param string $title
     * @param string $content
     */
    public function __construct(string $title, string $content)
    {
        $this->title   = $title;
        $this->content = $content;
    }
}
