<?php

namespace Proximum\Vimeet\Domain\Model;

interface TraceableInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getTraceableName();
}
