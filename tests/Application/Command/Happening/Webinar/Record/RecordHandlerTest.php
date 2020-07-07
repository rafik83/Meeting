<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Record;

use OpenTok\Archive;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\PrepareReconciliation;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\PrepareReconciliationHandler;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\Record;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\RecordHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Webinar\RecordArchive;
use Proximum\Vimeet\Domain\Repository\Happening\Webinar\RecordArchiveRepositoryInterface;

class RecordHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $happening = $this->prophesize(Happening::class);
        $event = $this->prophesize(Event::class);
        $happening->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $happening->getTitle('fr')->shouldBeCalled()->willReturn('Title of the happening');
        $happening->getWebinarSessionId()->shouldBeCalled()->willReturn('azerty');
        $event->getLocaleFallback()->shouldBeCalled()->willReturn('fr');

        $videoConferenceAdapter = $this->prophesize(VideoConferenceAdapterInterface::class);
        $recordArchiveRepository = $this->prophesize(RecordArchiveRepositoryInterface::class);
        $dateTime = new \DateTime('2020-10-10 10:00:00.000');

        $archive = new Archive(
            [
                'id' => '2516a93f-d04a-4ae9-b088-80efe9e48115',
                'partnerId' => 1234567,
                'sessionId' => 'azerty',
                'status' => 'started',
            ],
            [
                'apiKey' => 'azertyuiop',
                'apiSecret' => 'poiuytreza',
                'apiUrl' => 'https://example.net/azertyuiop',
            ]
        );
        $videoConferenceAdapter
            ->archive('azerty', 'Title of the happening')
            ->shouldBeCalled()
            ->willReturn($archive)
        ;

        $recordArchive = new RecordArchive(
            $happening->reveal(),
            '2516a93f-d04a-4ae9-b088-80efe9e48115',
            $dateTime
        );
        $recordArchiveRepository
            ->add($recordArchive)
            ->shouldBeCalled()
        ;

        $dueDate = clone $dateTime;
        $dueDate->modify('+ 125minutes');
        $prepareReconciliationHandler = $this->prophesize(PrepareReconciliationHandler::class);
        $prepareReconciliationHandler
            ->handle(new PrepareReconciliation($happening->reveal(), $dueDate))
            ->shouldBeCalled()
        ;

        $command = new Record($happening->reveal());
        $handler = new RecordHandler(
            $videoConferenceAdapter->reveal(),
            $recordArchiveRepository->reveal(),
            $prepareReconciliationHandler->reveal(),
            $dateTime
        );

        $handler->handle($command);
    }
}
