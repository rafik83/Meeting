<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\Webservice\Spot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot\CreateFile;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot\CreateFileHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;

class CreateFileHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime('2018-04-01 10:10:09');
        $exportPath = '/path/to/export';
        $expectedFilePath = '/path/to/export/export_spots_123_2018-04-01_10-10-09.csv';

        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(123);

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage
            ->create('my-content', 'export_spots_123_2018-04-01_10-10-09.csv', $exportPath)
            ->shouldBeCalled()
            ->willReturn($expectedFilePath)
        ;

        $expectedFile = new File($expectedFilePath, $dateTime);
        $fileRepository = $this->prophesize(FileRepositoryInterface::class);
        $fileRepository->add($expectedFile)->shouldBeCalled();

        $createFileHandler = new CreateFileHandler(
            $fileStorage->reveal(),
            $fileRepository->reveal(),
            $exportPath,
            $dateTime
        );
        $result = $createFileHandler->handle(new CreateFile($event->reveal(), 'my-content'));

        $this->assertEquals($expectedFile, $result);
    }
}
