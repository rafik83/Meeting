<?php

namespace Proximum\Vimeet\Tests\Application\Command\Encryption;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SheetDecryptFileInterface;
use Proximum\Vimeet\Application\Adapter\UserEventDecryptFileInterface;
use Proximum\Vimeet\Application\Command\Encryption\Decrypt;
use Proximum\Vimeet\Application\Command\Encryption\DecryptHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class DecryptHandlerTest extends TestCase
{
    private $event;
    private $sheet;
    private $user;
    private $sheetDecryptFile;
    private $userEventDecryptFile;
    private $decryptHandler;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getEvent()->willReturn($this->event->reveal());
        $this->user = $this->prophesize(User::class);

        $this->sheetDecryptFile     = $this->prophesize(SheetDecryptFileInterface::class);
        $this->userEventDecryptFile = $this->prophesize(UserEventDecryptFileInterface::class);
        $this->decryptHandler       = new DecryptHandler(
            $this->sheetDecryptFile->reveal(),
            $this->userEventDecryptFile->reveal()
        );
    }

    public function testSheetDecryptFile()
    {
        $this
            ->sheetDecryptFile
            ->decryptFile($this->sheet->reveal(), '/path/encryptedFile', '/path/file')
            ->shouldBeCalled()
        ;

        $this->decryptHandler->handle(
            new Decrypt($this->sheet->reveal(), $this->user->reveal(), true, '/path/encryptedFile', '/path/file')
        );
    }

    public function testUserEventDecryptFile()
    {
        $this
            ->userEventDecryptFile
            ->decryptFile($this->event->reveal(), $this->user->reveal(), '/path/encryptedFile', '/path/file')
            ->shouldBeCalled()
        ;

        $this->decryptHandler->handle(
            new Decrypt($this->sheet->reveal(), $this->user->reveal(), false, '/path/encryptedFile', '/path/file')
        );
    }
}
