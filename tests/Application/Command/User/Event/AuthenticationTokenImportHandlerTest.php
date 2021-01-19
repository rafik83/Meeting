<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\User\Event\AuthenticationTokenImport;
use Proximum\Vimeet\Application\Command\User\Event\AuthenticationTokenImportHandler;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;

class AuthenticationTokenImportHandlerTest extends TestCase
{
    public function testHandle()
    {
        $fileRepository = $this->prophesize(FileRepositoryInterface::class);
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $importDir =  __DIR__ . '/../../../Components/User/Event/import_token.csv';
        $dateTime = new \DateTime();
        $file = new File('/path/to/file', $dateTime);

        $fileStorage
            ->create(Argument::type('string'), 'import_token.csv', $importDir)
            ->shouldBeCalled()
            ->willReturn('/path/to/file');

        $fileRepository
            ->add($file)
            ->shouldBeCalled();

        $command = new AuthenticationTokenImport();
        $command->file = $importDir;

        $handler = new AuthenticationTokenImportHandler(
            $fileRepository->reveal(),
            $fileStorage->reveal(),
            $importDir,
            $dateTime
        );

        $handler->handle($command);
    }
}
