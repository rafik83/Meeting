<?php

namespace Proximum\Vimeet\Tests\Application\Command\Participant\Import;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Application\Command\Participant\Import;
use Proximum\Vimeet\Application\Command\Participant\ImportHandler;
use Proximum\Vimeet\Application\Components\Import\ParticipantImportTag;
use Proximum\Vimeet\Application\Serializer\Charset;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportHandlerTest extends TestCase
{
    public function handle()
    {
        $publicDir = '/var/participant_import';
        $filename = 'participant.csv';

        $uploadedFile = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs(
                [
                    tempnam(sys_get_temp_dir(), ''),
                    'csv',
                ]
            )
            ->getMock()
        ;

        $command = new Import();
        $command->file = $uploadedFile;

        // Mock
        $localFileStorageAdapter = $this->prophesize(FileStorageInterface::class);
        $session = $this->prophesize(SessionInterface::class);

        $localFileStorageAdapter->upload($uploadedFile, $publicDir)->shouldBeCalled()->willReturn($filename);

        $session->set(ParticipantImportTag::PARTICIPANT_IMPORT_FILE, $filename)->shouldBeCalled();
        $session->set(ParticipantImportTag::PARTICIPANT_IMPORT_CHARSET, Charset::WINDOWS_1252)->shouldBeCalled();

        $handler = new ImportHandler(
            $localFileStorageAdapter->reveal(),
            $session->reveal(),
            $publicDir
        );

        $handler->handle($command);
    }

    public function testFileNotFound()
    {
        $this->expectException(FileNotFoundException::class);

        $publicDir = '/var/participant_import';
        $filename = 'import.csv';
        $file = new UploadedFile($publicDir . $filename, 'file');

        $command = new Import();

        // Mock
        $localFileStorageAdapter = $this->prophesize(FileStorageInterface::class);
        $session = $this->prophesize(SessionInterface::class);

        $localFileStorageAdapter->upload($file, $publicDir)->shouldBeCalled();

        $session->set(ParticipantImportTag::PARTICIPANT_IMPORT_FILE, $filename)->shouldBeCalled();
        $session->set(ParticipantImportTag::PARTICIPANT_IMPORT_CHARSET, Charset::WINDOWS_1252)->shouldBeCalled();

        $handler = new ImportHandler(
            $localFileStorageAdapter->reveal(),
            $session->reveal(),
            $publicDir
        );

        $handler->handle($command);
    }
}
