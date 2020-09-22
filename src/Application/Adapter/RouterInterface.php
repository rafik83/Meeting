<?php

namespace Proximum\Vimeet\Application\Adapter;

use Symfony\Component\Routing\RequestContext;

interface RouterInterface
{
    public function generate($path, array $parameters = []): string;

    /**
     * Generate absolute url
     */
    public function url($path, array $parameters = []): string;

    public function getContext(): RequestContext;
}
