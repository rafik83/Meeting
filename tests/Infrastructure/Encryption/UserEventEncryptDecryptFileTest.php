<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Encryption;

use Defuse\Crypto\KeyProtectedByPassword;
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

        $keyProtectedByPassword = KeyProtectedByPassword::loadFromAsciiSafeString(
            'def10000def50200a502bf663225c1c6d6966a035fa62f3da80fc2dd4805aba302a4a4b21998dab7c07e104f8a9fc6898bfb6390c120b1ecf36b8534bdd58f5a245b8622ec288787e98f9f1fcc17dfd945f068a1d505fb128a5f9a1ec7a3886a7a81c15f532d64ae9abe80779aa99d3ca9865ed4a65cdff8aedb2105e941efadb59923e629dc046238220747fbdd7326d2937d07573f6e2a0ab7f22afa9a34287ebdb076f4e77a6c24f45d1aaab7ca1415dc48301072e981bb262e353776326214f2516f0ed95d22ee910d02953ff8dc69d075cb71476803ff19f7ded48f3e0b748e21072505670312074da6b99248b598a1a23c1217b0235088847bb0309717'
        );
        $userKey = $keyProtectedByPassword->unlockKey(
            '379f3769476d2c74878462dafa1faec6c65c7e0e6114f8ba43abcfb2d39bff3a'
        );

        $userEventKeyGetter = $this->prophesize(UserEventKeyGetter::class);
        $userEventKeyGetter
            ->getKeyByEventAndUser($event->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn($userKey)
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
