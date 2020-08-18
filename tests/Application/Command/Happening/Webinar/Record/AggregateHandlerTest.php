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

    public function testHandleWithSingleArchive(): void
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
