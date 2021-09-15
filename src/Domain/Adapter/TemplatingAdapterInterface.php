<?php

namespace Proximum\Vimeet\Domain\Adapter;

interface TemplatingAdapterInterface
{
    /**
     * Renders a template
     *
     * @param string $template
     * @param array  $context  The arguments
     *
     * @return string
     */
    public function render($template, array $context);
}
