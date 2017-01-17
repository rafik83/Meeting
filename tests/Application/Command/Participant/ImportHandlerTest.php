<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Application\Components\Import\ParticipantImportTag;
use Proximum\Vimeet\Application\Serializer\Charset;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function handle()
    {
        $publicDir = '/var/participant_import';
        $filename  = 'vimeet/src/Behat/Resources/fixtures/Files/dummy-image-test.jpg';
        $file      = new UploadedFile($publicDir . $filename, 'file');

//        $this
//            ->getMockBuilder(UploadedFile::class)
//            ->enableOriginalConstructor()
//            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'jpeg'])
//            ->getMock();

        $command = new Import();

        // Mock
        $localFileStorageAdapter = $this->prophesize(FileStorageInterface::class);
        $session                 = $this->prophesize(SessionInterface::class);

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
    
    public function testFileNotFound()
    {
        $this->expectException(FileNotFoundException::class);

        $publicDir = '/var/participant_import';
        $filename  = 'import.csv';
        $file      = new UploadedFile($publicDir . $filename, 'file');

        $command = new Import();

        // Mock
        $localFileStorageAdapter = $this->prophesize(FileStorageInterface::class);
        $session                 = $this->prophesize(SessionInterface::class);

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
