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
use Proximum\Vimeet\Application\Command\Participant\Upload\UploadFile;
use Proximum\Vimeet\Application\Command\Participant\Upload\UploadFileHandler;
use Proximum\Vimeet\Domain\Template\AbstractChild;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadFileHandlerTest extends TestCase
{
    public function testHandle()
    {
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

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload($uploadedFile)->shouldBeCalled()->willReturn('/my-directory/photo.jpg');

        $uploadObject = new UploadObject('myUploadObject', AbstractChild::TEMPLATE_OBJECT_TYPE_UPLOAD, [], 'fr', 'fr');
        $uploadObject->setFile($uploadedFile);

        $uploadFileHandler = new UploadFileHandler($fileStorage->reveal());
        $result = $uploadFileHandler->handle(
            new UploadFile(
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
