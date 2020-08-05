<?php

namespace Proximum\Vimeet\Tests\Application\Query\Happening;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ZipRecordArchiveStorageInterface;
use Proximum\Vimeet\Application\Query\Happening\Admin\DownloadWebinarQuery;
use Proximum\Vimeet\Application\Query\Happening\Admin\DownloadWebinarQueryHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Webinar\RecordArchive;
use Proximum\Vimeet\Domain\Repository\Happening\Webinar\RecordArchiveRepositoryInterface;

class DownloadWebinarQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $recordArchiveRepository;

    /** @var ObjectProphecy */
    private $zipRecordArchiveStorage;

    /** @var ObjectProphecy */
    private $fileSystem;

    /** @var DownloadWebinarQueryHandler */
    private $downloadWebinarQueryHandler;

    protected function setUp(): void
    {
        $this->recordArchiveRepository = $this->prophesize(RecordArchiveRepositoryInterface::class);
        $this->zipRecordArchiveStorage = $this->prophesize(ZipRecordArchiveStorageInterface::class);
        $this->fileSystem = $this->prophesize(FileSystemAdapterInterface::class);

        $this->downloadWebinarQueryHandler = new DownloadWebinarQueryHandler(
            $this->recordArchiveRepository->reveal(),
            $this->zipRecordArchiveStorage->reveal(),
            $this->fileSystem->reveal()
        );

    }

    public function testHandleWithSingleArchive()
    {
        $happening = $this->prophesize(Happening::class);
        $happening->getId()->shouldBeCalled()->willReturn(4321);
        $happening->isWebinarRecorded()->shouldBeCalled()->willReturn(true);

        $recordArchive = $this->prophesize(RecordArchive::class);
        $recordArchive->getPath()->shouldBeCalled()->willReturn('http://storage.fakeaws.com/xxx/yyy.mp4?token=xxxyz');

        $this->recordArchiveRepository
            ->getRecordArchivesForHappening($happening->reveal())
            ->shouldBeCalled()
            ->willReturn([$recordArchive->reveal()]);

        $this->zipRecordArchiveStorage->delete()->shouldNotBeCalled();
        $this->zipRecordArchiveStorage->download(Argument::any())->shouldNotBeCalled();

        $query = new DownloadWebinarQuery($happening->reveal());
        $fileTemporary = $this->downloadWebinarQueryHandler->handle($query);

        $this->assertEquals('http://storage.fakeaws.com/xxx/yyy.mp4?token=xxxyz', $fileTemporary->getTempFilePath());
        $this->assertEquals('webinar-4321.mp4', $fileTemporary->getOriginalName());
    }

    public function testHandleWithMultipleArchives()
    {
        $happening = $this->prophesize(Happening::class);
        $happening->getId()->shouldBeCalled()->willReturn(4321);
        $happening->isWebinarRecorded()->shouldBeCalled()->willReturn(true);

        $recordArchive1 = $this->prophesize(RecordArchive::class);
        $recordArchive2 = $this->prophesize(RecordArchive::class);

        $this->recordArchiveRepository
            ->getRecordArchivesForHappening($happening->reveal())
            ->shouldBeCalled()
            ->willReturn([$recordArchive1->reveal(), $recordArchive2->reveal()]);

        $this->fileSystem->getTemporaryPath()->shouldBeCalled()->willReturn('/tmp/vimeet/18428e41-e67c-4865-9ad3-6e50e18a94e9');
        $this->zipRecordArchiveStorage->delete()->shouldNotBeCalled();
        $this->zipRecordArchiveStorage
            ->download('multiple-archives/webinar-4321.zip', '/tmp/vimeet/18428e41-e67c-4865-9ad3-6e50e18a94e9')
            ->shouldBeCalled()
            ->willReturn(true);

        $query = new DownloadWebinarQuery($happening->reveal());
        $fileTemporary = $this->downloadWebinarQueryHandler->handle($query);

        $this->assertEquals('/tmp/vimeet/18428e41-e67c-4865-9ad3-6e50e18a94e9', $fileTemporary->getTempFilePath());
        $this->assertEquals('webinar-4321.zip', $fileTemporary->getOriginalName());
    }

    public function testInvalidWebinar()
    {
        $this->expectException(\RuntimeException::class);
        $happening = $this->prophesize(Happening::class);
        $happening->isWebinarRecorded()->shouldBeCalled()->willReturn(false);

        $query = new DownloadWebinarQuery($happening->reveal());
        $this->downloadWebinarQueryHandler->handle($query);
    }
}
