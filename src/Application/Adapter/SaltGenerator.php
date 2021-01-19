<?php

namespace Proximum\Vimeet\Application\Adapter;

class SaltGenerator implements SaltGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        return sha1(uniqid());
    }
}
