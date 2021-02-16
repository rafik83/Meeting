<?php

namespace Proximum\Vimeet\Application\Adapter;

interface SMSBlackListInterface
{
    /**
     * @return array
     */
    public function getBlackList(): array;
}
