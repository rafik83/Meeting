<?php

namespace Proximum\Vimeet\Application\Adapter;

interface ProtectedKeyInterface
{
    public function getKeyProtectedByPassword(string $password): string;
}
