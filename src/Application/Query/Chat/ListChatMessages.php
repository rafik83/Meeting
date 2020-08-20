<?php

namespace Proximum\Vimeet\Application\Query\Chat;

use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Model\User;

class ListChatMessages
{
    /** @var ChatMessageLinkableInterface */
    public $object;

    /** @var User */
    public $user;

    public function __construct(ChatMessageLinkableInterface $object, User $user)
    {
        $this->object = $object;
        $this->user = $user;
    }
}
