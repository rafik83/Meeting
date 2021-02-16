<?php

namespace Proximum\Vimeet\Application\View\Tip\Event;

class PreviewTipView
{
    /** @var string */
    public $title;

    /** @var string */
    public $content;

    /** @var array */
    public $pages;

    /**
     * PreviewTipView constructor.
     *
     * @param string $title
     * @param string $content
     * @param array  $pages
     */
    public function __construct($title, $content, array $pages)
    {
        $this->title   = $title;
        $this->content = $content;
        $this->pages   = $pages;
    }
}
