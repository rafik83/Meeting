<?php

namespace Proximum\Vimeet\Tests\Application\Command\OMZ;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\OMZ\PersistContent;
use Proximum\Vimeet\Application\Command\OMZ\PersistContentHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;

class PersistContentHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $event = $this->prophesize(Event::class);
        $content = 'omz;export;csv';
        $path = '/tmp/path/to/omz/export';

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileRepository = $this->prophesize(FileRepositoryInterface::class);

        $filePath = '/tmp/path/to/omz/export/omz_export.csv';
        $fileStorage->create($content, sprintf('export_participant_schedules_%s.csv', $dateTime->format('Y_m_d_His')), $path)
            ->shouldBeCalled()
            ->willReturn($filePath)
        ;

        $file = new File($filePath, $dateTime);
        $fileRepository->add($file)->shouldBeCalled();

        $command = new PersistContent($event->reveal(), $content);
        $handler = new PersistContentHandler(
            $fileStorage->reveal(),
            $fileRepository->reveal(),
            $path,
            $dateTime
        );

        $result = $handler->handle($command);

        $this->assertEquals($file, $result);
    }
}
