<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\ChatMessage;

interface ChatMessageRepositoryInterface
{
    public function add(ChatMessage $chatMessage): void;
}
