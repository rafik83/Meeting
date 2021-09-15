<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use Proximum\Vimeet\Application\Adapter\MarkdownAdapterInterface;

class Markdown implements MarkdownAdapterInterface
{
    /**
     * @var CommonMarkConverter
     */
    private $parser;

    /**
     * Markdown constructor.
     */
    public function __construct()
    {
        $environment = Environment::createCommonMarkEnvironment();
        $environment->addExtension(new AttributesExtension());
        $config = [
            'allow_unsafe_links' => false
        ];
        $this->parser = new CommonMarkConverter($config, $environment);
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function toHtml($text)
    {
        if (null === $text) {
            return '';
        }
        return $this->parser->convertToHtml($text);
    }
}
