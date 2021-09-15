<?php

namespace Proximum\Vimeet\Tests\Application\Command\Participant\Upload;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Encryption\Encrypt;
use Proximum\Vimeet\Application\Command\Encryption\EncryptHandler;
use Proximum\Vimeet\Application\Command\Participant\Upload\UploadFile;
use Proximum\Vimeet\Application\Command\Participant\Upload\UploadFileHandler;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\AbstractChild;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadFileHandlerTest extends TestCase
{
    public function testUploadAndEncryptFile()
    {
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);

        $uploadedFile = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'jpeg'])
            ->getMock();

        $uploadedFile
            ->expects(static::once())
            ->method('guessExtension')
            ->withAnyParameters()
            ->willReturn('jpg')
        ;

        $uploadObject = new UploadObject(
            'myUploadObject',
            AbstractChild::TEMPLATE_OBJECT_TYPE_UPLOAD,
            ['crypted' => true],
            'fr',
            'fr'
        );
        $uploadObject->setData(['path' => '/previous/file.jpg']);
        $uploadObject->setFile($uploadedFile);

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage
            ->remove('/path/previous/file.jpg', true)
            ->shouldBeCalled()
        ;
        $fileStorage->upload($uploadedFile, '/path')->shouldBeCalled()->willReturn('/my-directory/photo.jpg');
        $fileStorage
            ->remove('/path/my-directory/photo.jpg', true)
            ->shouldBeCalled()
        ;
        $fileStorage
            ->rename('/path/my-directory/photo.jpg_encrypted', '/path/my-directory/photo.jpg', true)
            ->shouldBeCalled()
        ;

        $encryptHandler = $this->prophesize(EncryptHandler::class);
        $encryptHandler
            ->handle(
                new Encrypt(
                    $sheet->reveal(),
                    $user->reveal(),
                    true,
                    '/path/my-directory/photo.jpg',
                    '/path/my-directory/photo.jpg_encrypted'
                )
            )
            ->shouldBeCalled()
        ;

        $uploadFileHandler = new UploadFileHandler(
            $encryptHandler->reveal(),
            $fileStorage->reveal(),
            '/path'
        );
        $result = $uploadFileHandler->handle(
            new UploadFile(
                $sheet->reveal(),
                $user->reveal(),
                $uploadObject,
                [
                    'whateverObject' => ['whateverValue'],
                    'myUploadObject' => [],
                    'anotherObject' => ['anotherValue'],
                ],
                true
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

    public function testUploadNotEncryptedFile()
    {
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);

        $uploadedFile = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'jpeg'])
            ->getMock();

        $uploadedFile
            ->expects(static::once())
            ->method('guessExtension')
            ->withAnyParameters()
            ->willReturn('jpg')
        ;

        $uploadObject = new UploadObject(
            'myUploadObject',
            AbstractChild::TEMPLATE_OBJECT_TYPE_UPLOAD,
            [],
            'fr',
            'fr'
        );
        $uploadObject->setData(['path' => '/previous/file.jpg']);
        $uploadObject->setFile($uploadedFile);

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage
            ->remove('/previous/file.jpg', false)
            ->shouldBeCalled()
        ;
        $fileStorage->upload($uploadedFile, null)->shouldBeCalled()->willReturn('/my-directory/photo.jpg');

        $encryptHandler = $this->prophesize(EncryptHandler::class);
        $encryptHandler->handle(Argument::any())->shouldNotBeCalled();

        $uploadFileHandler = new UploadFileHandler(
            $encryptHandler->reveal(),
            $fileStorage->reveal(),
            '/path'
        );
        $result = $uploadFileHandler->handle(
            new UploadFile(
                $sheet->reveal(),
                $user->reveal(),
                $uploadObject,
                [
                    'whateverObject' => ['whateverValue'],
                    'myUploadObject' => [],
                    'anotherObject' => ['anotherValue'],
                ],
                true
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
