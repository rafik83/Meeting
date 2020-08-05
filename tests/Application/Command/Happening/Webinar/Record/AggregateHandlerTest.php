<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Record;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Adapter\FinderAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ZipArchiveAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ZipRecordArchiveStorageInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\Aggregate;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\AggregateHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Webinar\RecordArchive;
use Proximum\Vimeet\Domain\Repository\Happening\Webinar\RecordArchiveRepositoryInterface;
use Symfony\Component\Finder\SplFileInfo;

class AggregateHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $recordArchiveRepository;

    /** @var ObjectProphecy */
    private $zipArchiveAdapter;

    /** @var ObjectProphecy */
    private $zipRecordArchiveStorage;

    /** @var ObjectProphecy */
    private $finderAdapter;

    /** @var ObjectProphecy */
    private $fileSystemAdapter;

    /** @var AggregateHandler */
    private $aggregateHandler;

    protected function setUp(): void
    {
        $this->recordArchiveRepository = $this->prophesize(RecordArchiveRepositoryInterface::class);
        $this->zipArchiveAdapter = $this->prophesize(ZipArchiveAdapterInterface::class);
        $this->zipRecordArchiveStorage = $this->prophesize(ZipRecordArchiveStorageInterface::class);
        $this->finderAdapter = $this->prophesize(FinderAdapterInterface::class);
        $this->fileSystemAdapter = $this->prophesize(FileSystemAdapterInterface::class);

        $this->aggregateHandler = new AggregateHandler(
            $this->recordArchiveRepository->reveal(),
            $this->zipArchiveAdapter->reveal(),
            $this->zipRecordArchiveStorage->reveal(),
            $this->finderAdapter->reveal(),
            $this->fileSystemAdapter->reveal()
        );
    }

    public function testHandle()
    {
        $happening = $this->prophesize(Happening::class);
        $happening->getId()->shouldBeCalled()->willreturn(5432);
        $recordArchive1 = $this->prophesize(RecordArchive::class);
        $recordArchive1->getPath()->shouldBeCalled()->willReturn('https://www.fakeawss3.com/video/xx32-de9.mp4?t=8833447878');
        $recordArchive2 = $this->prophesize(RecordArchive::class);
        $recordArchive2->getPath()->shouldBeCalled()->willReturn('https://www.fakeawss3.com/video/ww78-ed7.mp4?t=7766551122');

        $this->recordArchiveRepository
            ->getRecordArchivesForHappening($happening->reveal())
            ->shouldBeCalled()
            ->willReturn([$recordArchive1->reveal(), $recordArchive2->reveal()]);

        $this->fileSystemAdapter->createTempDir()->shouldBeCalled()->willReturn('/tmp/vimeet/18428e41-e67c-4865-9ad3-6e50e18a94e9');
        $this->fileSystemAdapter->copy(Argument::type('string'), Argument::type('string'))->shouldBeCalledTimes(2);
        $file = $this->prophesize(SplFileInfo::class);
        $this->finderAdapter->filesIn('/tmp/vimeet/18428e41-e67c-4865-9ad3-6e50e18a94e9')->shouldBeCalled()->willReturn([$file]);
        $this->fileSystemAdapter->getTemporaryPath()->shouldBeCalled()->willReturn('/tmp/vimeet/2983e194-47cd-4b2c-89ae-e7d8cb4b8312');
        $this->zipArchiveAdapter->zipFiles([$file], '/tmp/vimeet/2983e194-47cd-4b2c-89ae-e7d8cb4b8312', '')->shouldBeCalled();
        $this->zipRecordArchiveStorage->upload('/tmp/vimeet/2983e194-47cd-4b2c-89ae-e7d8cb4b8312', 'multiple-archives/webinar-5432.zip')->shouldBeCalled();
        $this->fileSystemAdapter->remove('/tmp/vimeet/18428e41-e67c-4865-9ad3-6e50e18a94e9')->shouldBeCalled();

        $command = new Aggregate($happening->reveal());
        $fileTemporary = $this->aggregateHandler->handle($command);

        $this->assertEquals('/tmp/vimeet/2983e194-47cd-4b2c-89ae-e7d8cb4b8312', $fileTemporary->getTempFilePath());
        $this->assertEquals('webinar-5432.zip', $fileTemporary->getOriginalName());
    }

    public function testHandleWithSingleArchive()
    {
        $happening = $this->prophesize(Happening::class);
        $recordArchive1 = $this->prophesize(RecordArchive::class);

        $this->recordArchiveRepository
            ->getRecordArchivesForHappening($happening->reveal())
            ->shouldBeCalled()
            ->willReturn([$recordArchive1->reveal()]);

        $this->zipArchiveAdapter->zipFiles()->shouldNotBeCalled();
        $this->zipRecordArchiveStorage->upload()->shouldNotBeCalled();

        $command = new Aggregate($happening->reveal());
        $this->aggregateHandler->handle($command);
    }
}
