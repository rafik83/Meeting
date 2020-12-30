<?php

namespace Proximum\Vimeet\Domain\View\Content;

class TermsOfSaleView
{
    /** @var string */
    public $content;

    /**
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->content = $content;
    }
}
