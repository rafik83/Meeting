<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Encryption;

use Defuse\Crypto\File;
use Proximum\Vimeet\Application\Adapter\UserEventEncryptFileInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class UserEventEncryptFileAdapter implements UserEventEncryptFileInterface
{
    /** @var UserEventKeyGetter */
    private $userEventKeyGetter;

    public function __construct(UserEventKeyGetter $userEventKeyGetter)
    {
        $this->userEventKeyGetter = $userEventKeyGetter;
    }

    public function encryptFile(Event $event, User $user, string $initialFilePath, string $encryptedFilePath): void
    {
        $userKey = $this->userEventKeyGetter->getKeyByEventAndUser($event, $user);

        File::encryptFile($initialFilePath, $encryptedFilePath, $userKey);
    }
}
