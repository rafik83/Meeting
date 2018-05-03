<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Participant\Upload;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\UserEventEncryptFileInterface;
use Proximum\Vimeet\Application\Command\Participant\Upload\UploadFile;
use Proximum\Vimeet\Application\Command\Participant\Upload\UploadFileHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\AbstractChild;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadFileHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $uploadedFile = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'jpeg'])
            ->getMock();

        $uploadedFile
            ->expects(static::once())
            ->method('getClientOriginalExtension')
            ->withAnyParameters()
            ->willReturn('jpg')
        ;

        $uploadedFile
            ->expects(static::once())
            ->method('getPathname')
            ->withAnyParameters()
            ->willReturn('/my-directory/photo.jpg')
        ;

        $uploadObject = new UploadObject(
            'myUploadObject',
            AbstractChild::TEMPLATE_OBJECT_TYPE_UPLOAD,
            ['crypted' => true],
            'fr',
            'fr'
        );
        $uploadObject->setFile($uploadedFile);

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload($uploadedFile)->shouldBeCalled()->willReturn('/my-directory/photo.jpg');

        $userEventEncryptFile = $this->prophesize(UserEventEncryptFileInterface::class);
        $userEventEncryptFile->encryptFile($event, $user, '/my-directory/photo.jpg', '/my-directory/photo.jpg');

        $uploadFileHandler = new UploadFileHandler($fileStorage->reveal(), $userEventEncryptFile->reveal());
        $result = $uploadFileHandler->handle(
            new UploadFile(
                $event->reveal(),
                $user->reveal(),
                $uploadObject,
                [
                    'whateverObject' => ['whateverValue'],
                    'myUploadObject' => [],
                    'anotherObject' => ['anotherValue'],
                ]
            )
        );

        $this->assertEquals(
            $result,
            [
                'whateverObject' => ['whateverValue'],
                'myUploadObject' => ['path' => '/my-directory/photo.jpg', 'extension' => 'jpg'],
                'anotherObject' => ['anotherValue'],
            ]
        );
    }
}
