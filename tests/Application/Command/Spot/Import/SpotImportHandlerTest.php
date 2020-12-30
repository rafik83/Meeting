<?php

namespace Proximum\Vimeet\Tests\Application\Command\Spot\Import;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Spot\Import\SpotImport;
use Proximum\Vimeet\Application\Command\Spot\Import\SpotImportHandler;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;

class SpotImportHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $fileRepository;

    /** @var ObjectProphecy */
    private $fileStorage;

    /** @var string */
    private $importDir;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function setUp()
    {
        $this->fileRepository = $this->prophesize(FileRepositoryInterface::class);
        $this->fileStorage = $this->prophesize(FileStorageInterface::class);
        $this->importDir =  __DIR__ . '/import_spot.csv';
        $this->dateTime = new \DateTime();
    }

    public function testHandle()
    {
        $file = new File('/path/to/file', $this->dateTime);

        $this
            ->fileStorage
            ->create(Argument::type('string'), 'import_spot.csv', $this->importDir)
            ->shouldBeCalled()
            ->willReturn('/path/to/file')
        ;

        $this
            ->fileRepository
            ->add($file)
            ->shouldBeCalled()
        ;

        $command = new SpotImport();
        $command->file = $this->importDir;
        $handler = new SpotImportHandler(
            $this->fileRepository->reveal(),
            $this->fileStorage->reveal(),
            $this->importDir,
            $this->dateTime
        );

        $handler->handle($command);
    }
}
