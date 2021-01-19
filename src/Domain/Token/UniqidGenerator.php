<?php

namespace Proximum\Vimeet\Domain\Token;

class UniqidGenerator
{
    /**
     * @return string
     */
    public function generate()
    {
        return uniqid(mt_rand());
    }
}
