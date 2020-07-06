<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Record;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\StopRecord;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\StopRecordHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Webinar\RecordArchive;
use Proximum\Vimeet\Domain\Repository\Happening\Webinar\RecordArchiveRepositoryInterface;

class StopRecordHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $dateTime = new \DateTime();
        $happening = $this->prophesize(Happening::class);
        $videoConferenceAdapter = $this->prophesize(VideoConferenceAdapterInterface::class);
        $recordArchiveRepository = $this->prophesize(RecordArchiveRepositoryInterface::class);

        $recordArchive1 = new RecordArchive($happening->reveal(), 'archive-id-1', $dateTime);
        $recordArchive2 = new RecordArchive($happening->reveal(), 'archive-id-2', $dateTime);
        $recordArchives = [
            $recordArchive1,
            $recordArchive2,
        ];
        $recordArchiveRepository->getStartedRecordArchiveForHappening($happening->reveal())
            ->shouldBeCalled()
            ->willReturn($recordArchives)
        ;

        $expectedRecordArchive1 = new RecordArchive($happening->reveal(), 'archive-id-1', $dateTime);
        $expectedRecordArchive1->stop();
        $expectedRecordArchive2 = new RecordArchive($happening->reveal(), 'archive-id-2', $dateTime);
        $expectedRecordArchive2->stop();

        $videoConferenceAdapter
            ->stopArchive('archive-id-1')
            ->shouldBeCalled()
        ;

        $videoConferenceAdapter
            ->stopArchive('archive-id-2')
            ->shouldBeCalled()
        ;

        $recordArchiveRepository->update($expectedRecordArchive1)->shouldBeCalled();
        $recordArchiveRepository->update($expectedRecordArchive2)->shouldBeCalled();

        $stopRecord = new StopRecord($happening->reveal());
        $handler = new StopRecordHandler(
            $videoConferenceAdapter->reveal(),
            $recordArchiveRepository->reveal()
        );

        $handler->handle($stopRecord);
    }
}
