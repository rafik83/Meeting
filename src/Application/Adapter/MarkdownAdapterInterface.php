<?php

namespace Proximum\Vimeet\Application\Adapter;

interface MarkdownAdapterInterface
{
    /**
     * @param string $text
     *
     * @return string
     */
    public function toHtml($text);
}
