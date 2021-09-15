<?php

namespace Proximum\Vimeet\Infrastructure\Encryption;

use Defuse\Crypto\KeyProtectedByPassword;
use Proximum\Vimeet\Application\Adapter\ProtectedKeyInterface;

class ProtectedKeyAdapter implements ProtectedKeyInterface
{
    public function getKeyProtectedByPassword(string $password): string
    {
        $protectedKeyByPassword = KeyProtectedByPassword::createRandomPasswordProtectedKey($password);

        return $protectedKeyByPassword->saveToAsciiSafeString();
    }
}
