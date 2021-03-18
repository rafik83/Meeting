<?php

namespace Proximum\Vimeet\Application\Nomenclature\Id;

class UniquIdGenerator implements IdGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        return uniqid('u');
    }
}
