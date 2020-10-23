<?php

namespace Proximum\Vimeet\Application\Command\Chat;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class AddChatMessage implements Command
{
    /** @var ChatMessageLinkableInterface */
    public $object;

    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /** @var string */
    public $content;

    public function __construct(
        ChatMessageLinkableInterface $object,
        User $user,
        Sheet $sheet,
        string $content
    ) {
        $this->object = $object;
        $this->user = $user;
        $this->sheet = $sheet;
        $this->content = $content;
    }
}
