<?php

namespace Proximum\Vimeet\Application\Adapter;

use Symfony\Component\Routing\RequestContext;

interface RouterInterface
{
    public function generate($path, array $parameters = []): string;

    public function generateAbsoluteUrl($path, array $parameters = []): string;

    public function initScheme(): void;

    public function getContext(): RequestContext;
}
