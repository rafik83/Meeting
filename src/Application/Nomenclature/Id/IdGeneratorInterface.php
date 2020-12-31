<?php

namespace Proximum\Vimeet\Application\Nomenclature\Id;

interface IdGeneratorInterface
{
    /**
     * @return string
     */
    public function generate();
}
