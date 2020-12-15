<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Record;

use OpenTok\Archive;
use OpenTok\ArchiveList;
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
        $archiveList = new ArchiveList(
            [
                'count' => 3,
                'items' => [
                    [
                        'id' => '2516a93f-d04a-4ae9-b088-80efe9e48115',
                        'partnerId' => 1234567,
                        'sessionId' => 'azerty',
                        'status' => 'started',
                        'url' => null,
                    ],
                    [
                        'id' => '2516a93f-d04a-4ae9-b088-80efe9e48116',
                        'partnerId' => 1234567,
                        'sessionId' => 'azerty',
                        'status' => 'started',
                        'url' => null,
                    ],
                    [
                        'id' => '2516a93f-d04a-4ae9-b088-80efe9e48117',
                        'partnerId' => 1234567,
                        'sessionId' => 'azerty',
                        'status' => 'stopped',
                        'url' => 'http://example.net/path/to/file.mp4'
                    ],
                ]
            ],
            [
                'apiKey' => 'azertyuiop',
                'apiSecret' => 'poiuytreza',
                'apiUrl' => 'https://example.net/azertyuiop',
            ]
        );

        $dateTime = new \DateTime();
        $happening = $this->prophesize(Happening::class);
        $happening->getWebinarSessionId()->shouldBeCalled()->willReturn('session-id');
        $videoConferenceAdapter = $this->prophesize(VideoConferenceAdapterInterface::class);
        $recordArchiveRepository = $this->prophesize(RecordArchiveRepositoryInterface::class);

        $recordArchive1 = new RecordArchive($happening->reveal(), '2516a93f-d04a-4ae9-b088-80efe9e48115', $dateTime);
        $recordArchive2 = new RecordArchive($happening->reveal(), '2516a93f-d04a-4ae9-b088-80efe9e48116', $dateTime);
        $recordArchives = [
            $recordArchive1,
            $recordArchive2,
        ];

        $recordArchiveRepository->getRecordArchivesForHappening($happening->reveal())
            ->shouldBeCalled()
            ->willReturn($recordArchives)
        ;
        $videoConferenceAdapter->listArchives('session-id')
            ->shouldBeCalled()
            ->willReturn($archiveList)
        ;

        $expectedRecordArchive1 = new RecordArchive(
            $happening->reveal(),
            '2516a93f-d04a-4ae9-b088-80efe9e48115',
            $dateTime
        );
        $expectedRecordArchive1->stop();
        $expectedRecordArchive2 = new RecordArchive(
            $happening->reveal(),
            '2516a93f-d04a-4ae9-b088-80efe9e48116',
            $dateTime
        );
        $expectedRecordArchive2->stop();

        $expectedRecordArchive3 = new RecordArchive(
            $happening->reveal(),
            '2516a93f-d04a-4ae9-b088-80efe9e48117',
            $dateTime
        );
        $expectedRecordArchive3->stop();

        $videoConferenceAdapter
            ->stopArchive('2516a93f-d04a-4ae9-b088-80efe9e48115')
            ->shouldBeCalled()
        ;

        $videoConferenceAdapter
            ->stopArchive('2516a93f-d04a-4ae9-b088-80efe9e48116')
            ->shouldBeCalled()
        ;

        $videoConferenceAdapter
            ->stopArchive('2516a93f-d04a-4ae9-b088-80efe9e48117')
            ->shouldNotBeCalled()
        ;

        $recordArchiveRepository->update($expectedRecordArchive1)->shouldBeCalled();
        $recordArchiveRepository->update($expectedRecordArchive2)->shouldBeCalled();
        $recordArchiveRepository->add($expectedRecordArchive3)->shouldBeCalled();

        $stopRecord = new StopRecord($happening->reveal());
        $handler = new StopRecordHandler(
            $videoConferenceAdapter->reveal(),
            $recordArchiveRepository->reveal(),
            $dateTime
        );

        $handler->handle($stopRecord);
    }
}
