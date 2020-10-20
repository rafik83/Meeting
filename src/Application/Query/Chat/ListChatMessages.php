<?php

namespace Proximum\Vimeet\Application\Query\Chat;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Model\User;

class ListChatMessages implements Query
{
    /** @var ChatMessageLinkableInterface */
    public $object;

    /** @var User */
    public $user;

    /** @var string */
    public $locale;

    public function __construct(ChatMessageLinkableInterface $object, User $user, string $locale)
    {
        $this->object = $object;
        $this->user = $user;
        $this->locale = $locale;
    }
}
