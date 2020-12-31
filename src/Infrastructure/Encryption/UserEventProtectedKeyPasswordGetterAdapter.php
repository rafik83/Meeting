<?php

namespace Proximum\Vimeet\Infrastructure\Encryption;

use Proximum\Vimeet\Application\Adapter\UserEventProtectedKeyPasswordGetterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class UserEventProtectedKeyPasswordGetterAdapter implements UserEventProtectedKeyPasswordGetterInterface
{
    /** @var string */
    private $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function getProtectedKeyPasswordByEventAndUser(Event $event, User $user): string
    {
        return hash('sha256', sprintf('_%d_%d_%s_', $event->getId(), $user->getId(), $this->secretKey));
    }
}
