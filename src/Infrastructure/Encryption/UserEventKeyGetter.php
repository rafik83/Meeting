<?php

namespace Proximum\Vimeet\Infrastructure\Encryption;

use Defuse\Crypto\Key;
use Defuse\Crypto\KeyProtectedByPassword;
use Proximum\Vimeet\Application\Adapter\UserEventProtectedKeyGetterInterface;
use Proximum\Vimeet\Application\Adapter\UserEventProtectedKeyPasswordGetterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class UserEventKeyGetter
{
    /** @var UserEventProtectedKeyGetterInterface */
    private $userEventProtectedKeyGetter;

    /** @var UserEventProtectedKeyPasswordGetterInterface */
    private $userEventProtectedKeyPasswordGetter;

    public function __construct(
        UserEventProtectedKeyGetterInterface $userEventProtectedKeyGetter,
        UserEventProtectedKeyPasswordGetterInterface $userEventProtectedKeyPasswordGetter
    ) {
        $this->userEventProtectedKeyGetter = $userEventProtectedKeyGetter;
        $this->userEventProtectedKeyPasswordGetter = $userEventProtectedKeyPasswordGetter;
    }

    public function getKeyByEventAndUser(Event $event, User $user): Key
    {
        $protectedKey = $this->userEventProtectedKeyGetter->getProtectedKeyByEventAndUser($event, $user);
        $protectedKeyPassword = $this->userEventProtectedKeyPasswordGetter->getProtectedKeyPasswordByEventAndUser(
            $event,
            $user
        );

        $keyProtectedByPassword = KeyProtectedByPassword::loadFromAsciiSafeString($protectedKey);

        return $keyProtectedByPassword->unlockKey($protectedKeyPassword);
    }
}
