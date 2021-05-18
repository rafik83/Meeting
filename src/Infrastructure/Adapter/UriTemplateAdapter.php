<?php


namespace Proximum\Vimeet\Infrastructure\Adapter;

use League\Uri\UriTemplate;
use Proximum\Vimeet\Application\Adapter\UriTemplateInterface;

class UriTemplateAdapter implements UriTemplateInterface
{
    /**
     * @throws \League\Uri\Contracts\UriException
     */
    public function render($uriTemplate, $variables): string {
        $template = new UriTemplate($uriTemplate, $variables);
        $uri = $template->expand([]);
        return $uri->__toString();
    }

}

