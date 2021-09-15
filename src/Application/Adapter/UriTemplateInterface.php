<?php


namespace Proximum\Vimeet\Application\Adapter;

interface UriTemplateInterface
{
    public function render($uriTemplate, $variables);
}

