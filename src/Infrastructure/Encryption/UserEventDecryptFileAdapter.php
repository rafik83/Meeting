<?php

namespace Proximum\Vimeet\Infrastructure\Encryption;

use Defuse\Crypto\File;
use Proximum\Vimeet\Application\Adapter\UserEventDecryptFileInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class UserEventDecryptFileAdapter implements UserEventDecryptFileInterface
{
    /** @var UserEventKeyGetter */
    private $userEventKeyGetter;

    public function __construct(UserEventKeyGetter $userEventKeyGetter)
    {
        $this->userEventKeyGetter = $userEventKeyGetter;
    }

    public function decryptFile(Event $event, User $user, string $encryptedFilePath, string $decryptedFilePath): void
    {
        $key = $this->userEventKeyGetter->getKeyByEventAndUser($event, $user);
        File::decryptFile($encryptedFilePath, $decryptedFilePath, $key);
    }
}
