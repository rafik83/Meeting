<?php

namespace Proximum\Vimeet\Tests\Application\Command\Encryption;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SheetEncryptFileInterface;
use Proximum\Vimeet\Application\Adapter\UserEventEncryptFileInterface;
use Proximum\Vimeet\Application\Command\Encryption\Encrypt;
use Proximum\Vimeet\Application\Command\Encryption\EncryptHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class EncryptHandlerTest extends TestCase
{
    private $event;
    private $sheet;
    private $user;
    private $sheetEncryptFile;
    private $userEventEncryptFile;
    private $encryptHandler;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getEvent()->willReturn($this->event->reveal());
        $this->user = $this->prophesize(User::class);

        $this->sheetEncryptFile = $this->prophesize(SheetEncryptFileInterface::class);
        $this->userEventEncryptFile = $this->prophesize(UserEventEncryptFileInterface::class);
        $this->encryptHandler = new EncryptHandler(
            $this->sheetEncryptFile->reveal(),
            $this->userEventEncryptFile->reveal()
        );
    }

    public function testSheetEncryptFile()
    {
        $this
            ->sheetEncryptFile
            ->encryptFile($this->sheet->reveal(), '/path/file', '/path/encryptedFile')
            ->shouldBeCalled()
        ;

        $this->encryptHandler->handle(
            new Encrypt($this->sheet->reveal(), $this->user->reveal(), true, '/path/file', '/path/encryptedFile')
        );
    }

    public function testUserEventEncryptFile()
    {
        $this
            ->userEventEncryptFile
            ->encryptFile($this->event->reveal(), $this->user->reveal(), '/path/file', '/path/encryptedFile')
            ->shouldBeCalled()
        ;

        $this->encryptHandler->handle(
            new Encrypt($this->sheet->reveal(), $this->user->reveal(), false, '/path/file', '/path/encryptedFile')
        );
    }
}
