<?php

namespace Proximum\Vimeet\Application\Query\Chat;

use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;

class ListChatMessagesHandler
{
    /** @var ChatMessageRepositoryInterface */
    private $chatMessageRepository;

    public function __construct(ChatMessageRepositoryInterface $chatMessageRepository)
    {
        $this->chatMessageRepository = $chatMessageRepository;
    }

    public function handle(ListChatMessages $query)
    {

    }
}
