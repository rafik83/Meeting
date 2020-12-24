<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service;

use Proximum\Vimeet\Application\Adapter\MarkdownAdapterInterface;

class Markdown implements MarkdownAdapterInterface
{
    /**
     * @var \Parsedown
     */
    private $parser;

    /**
     * Markdown constructor.
     */
    public function __construct()
    {
        $this->parser = new \Parsedown();
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function toHtml($text)
    {
        $this->parser->setSafeMode(true);

        return $this->parser->text($text);
    }
}
