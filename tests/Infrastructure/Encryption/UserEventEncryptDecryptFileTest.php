<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Encryption;

use Defuse\Crypto\Key;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Encryption\UserEventDecryptFileAdapter;
use Proximum\Vimeet\Infrastructure\Encryption\UserEventEncryptFileAdapter;
use Proximum\Vimeet\Infrastructure\Encryption\UserEventKeyGetter;

class UserEventEncryptDecryptFileTest extends TestCase
{
    public function testEncryptDecryptFile()
    {
        $path = __DIR__ . '/Fixture/';
        $filename = 'user-story-meme.jpg';
        $initialFile = $path . $filename;

        $encryptedFilename = uniqid(mt_rand());
        $encryptedFile = $path . $encryptedFilename;
        $decryptedFile = $path . 'decrypted.jpg';

        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $key = Key::createNewRandomKey();

        $userEventKeyGetter = $this->prophesize(UserEventKeyGetter::class);
        $userEventKeyGetter
            ->getKeyByEventAndUser($event->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn($key)
        ;

        $userEventEncryptFileAdapter = new UserEventEncryptFileAdapter($userEventKeyGetter->reveal());
        $userEventEncryptFileAdapter->encryptFile($event->reveal(), $user->reveal(), $initialFile, $encryptedFile);

        $this->assertFileExists($encryptedFile);
        $this->assertNotSame(md5_file($encryptedFile), md5_file($initialFile));

        $userEventDecryptFileAdapter = new UserEventDecryptFileAdapter($userEventKeyGetter->reveal());
        $userEventDecryptFileAdapter->decryptFile($event->reveal(), $user->reveal(), $encryptedFile, $decryptedFile);

        $this->assertFileExists($decryptedFile);
        $this->assertSame(md5_file($decryptedFile), md5_file($initialFile));

        unlink($encryptedFile);
        unlink($decryptedFile);
    }
}
