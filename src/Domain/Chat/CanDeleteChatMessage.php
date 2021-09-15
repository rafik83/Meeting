<?php

namespace Proximum\Vimeet\Domain\Chat;

use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class CanDeleteChatMessage
{
    public function isSatisfiedBy(ChatMessageLinkableInterface $context, User $user): bool
    {
        if (!$context instanceof Happening) {
            return false;
        }

        if (!$context->hasSpeaker($user)) {
            return false;
        }

        return true;
    }
}
