<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

interface UserEventEncryptFileInterface
{
    public function encryptFile(Event $event, User $user, string $initialFilePath, string $encryptedFilePath): void;
}
