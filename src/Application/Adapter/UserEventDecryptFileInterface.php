<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

interface UserEventDecryptFileInterface
{
    public function decryptFile(Event $event, User $user, string $encryptedFilePath, string $decryptedFilePath): void;
}
