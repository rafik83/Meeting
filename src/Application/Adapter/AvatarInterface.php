<?php

namespace Proximum\Vimeet\Application\Adapter;

interface AvatarInterface
{
    public function generate(string $name): string;
}
