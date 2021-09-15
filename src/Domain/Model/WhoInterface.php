<?php

namespace Proximum\Vimeet\Domain\Model;

interface WhoInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getIdentifier();
}
