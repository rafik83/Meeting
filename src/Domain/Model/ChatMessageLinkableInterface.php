<?php

namespace Proximum\Vimeet\Domain\Model;

interface ChatMessageLinkableInterface
{
    /**
     * @return int
     */
    public function getId();

    public function getObjectType(): string;
}
