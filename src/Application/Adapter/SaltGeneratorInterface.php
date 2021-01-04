<?php

namespace Proximum\Vimeet\Application\Adapter;

interface SaltGeneratorInterface
{
    /**
     * @return string
     */
    public function generate();
}
