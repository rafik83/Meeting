<?php

namespace Proximum\Vimeet\Tests\Application\Command\Planner;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\ExecInterface;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Command\MeetingRequest\Admin\LockMeetingRequestUpdate;
use Proximum\Vimeet\Application\Command\MeetingRequest\Admin\LockMeetingRequestUpdateHandler;
use Proximum\Vimeet\Application\Command\Planner\Export;
use Proximum\Vimeet\Application\Command\Planner\ExportHandler;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\Dispatcher;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\DispatcherHandler;
use Proximum\Vimeet\Application\Query\Planner\PlannerViewQuery;
use Proximum\Vimeet\Application\Query\Planner\PlannerViewQueryHandler;
use Proximum\Vimeet\Application\View\Planner\PlannerView;
use Proximum\Vimeet\Domain\File\FileFactory;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Model\PlannerJob;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PlannerJobRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\ExportPlannerMail;

class ExportHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $lockMeetingRequestHandler;

    /** @var ObjectProphecy */
    private $dispatcherHandler;

    /** @var ObjectProphecy */
    private $plannerHandler;

    /** @var ObjectProphecy */
    private $serializer;

    /** @var ObjectProphecy */
    private $fileStorageAdapter;

    /** @var string */
    private $exportLocationDirectoryPath;

    /** @var string */
    private $plannerFilesPath;

    /** @var string */
    private $plannerCommand;

    /** @var ObjectProphecy */
    private $eventRepository;

    /** @var ObjectProphecy */
    private $plannerJobRepository;

    /** @var ObjectProphecy */
    private $mailer;

    /** @var string */
    private $mailSender;

    /** @var ExportHandler */
    private $exportHandler;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $execAdapter;

    /** @var ObjectProphecy */
    private $fileFactory;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->event->getId()->willReturn(1337);
        $this->event->getAvailableLocale('fr')->willReturn('fr');

        $this->eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $this->eventRepository->getById(1337)->willReturn($this->event->reveal());

        $this->lockMeetingRequestHandler = $this->prophesize(LockMeetingRequestUpdateHandler::class);
        $this->dispatcherHandler = $this->prophesize(DispatcherHandler::class);
        $this->plannerHandler = $this->prophesize(PlannerViewQueryHandler::class);
        $this->serializer = $this->prophesize(SerializerAdapterInterface::class);
        $this->fileStorageAdapter = $this->prophesize(FileStorageInterface::class);
        $this->exportLocationDirectoryPath = '/path/export';
        $this->plannerFilesPath = '/var/planner';
        $this->plannerCommand = 'curl -v -X POST http://optaplanner/job/PLANNER/build --data-urlencode json=\'{"parameter": [{"name":"INPUT", "value":"%filename%"}]}\'';
        $this->plannerJobRepository = $this->prophesize(PlannerJobRepositoryInterface::class);
        $this->mailer = $this->prophesize(MailerInterface::class);
        $this->mailSender = 'from@vimeet.events';
        $this->execAdapter = $this->prophesize(ExecInterface::class);
        $this->fileFactory = $this->prophesize(FileFactory::class);

        $this->exportHandler = new ExportHandler(
            $this->lockMeetingRequestHandler->reveal(),
            $this->dispatcherHandler->reveal(),
            $this->plannerHandler->reveal(),
            $this->serializer->reveal(),
            $this->fileStorageAdapter->reveal(),
            $this->exportLocationDirectoryPath,
            $this->plannerFilesPath,
            $this->plannerCommand,
            $this->eventRepository->reveal(),
            $this->plannerJobRepository->reveal(),
            $this->mailer->reveal(),
            $this->mailSender,
            $this->execAdapter->reveal(),
            $this->fileFactory->reveal()
        );
    }

    public function test_mode_auto()
    {
        $solutionType = 'moving_allowed';

        $plannerJob = $this->prophesize(PlannerJob::class);
        $this->plannerJobRepository->getById(42)->willReturn($plannerJob->reveal());

        $this->dispatcherHandler
            ->handle(new Dispatcher($this->event->reveal()))
            ->shouldBeCalled()
        ;

        $plannerView = $this->prophesize(PlannerView::class);
        $this->plannerHandler
            ->handle(new PlannerViewQuery($this->event->reveal(), 'fr', $solutionType))
            ->shouldBeCalled()
            ->willReturn($plannerView->reveal())
        ;

        $serializedPlanner = 'Serialized planner';
        $this->serializer
            ->serialize($plannerView->reveal(), 'xml', ['xml_root_node_name' => 'MeetingSchedule'])
            ->shouldBeCalled()
            ->willReturn($serializedPlanner)
        ;

        $this->lockMeetingRequestHandler
            ->handle(new LockMeetingRequestUpdate($this->event->reveal(), true))
            ->shouldBeCalled()
        ;

        $fileFullPath = '/var/planner/2018/12/planner_1337.xml';
        $this->fileStorageAdapter
            ->create($serializedPlanner, 'planner_1337.xml', $this->plannerFilesPath)
            ->shouldBeCalled()
            ->willReturn($fileFullPath)
        ;

        $file = $this->prophesize(File::class);
        $file->getPath()->shouldBeCalled()->willReturn($fileFullPath);
        $this->fileFactory
            ->createAndPersistFile($fileFullPath)
            ->shouldBeCalled()
            ->willReturn($file->reveal())
        ;

        $output = [];
        $result = 0;
        $this->execAdapter
            ->exec(
                'curl -v -X POST http://optaplanner/job/PLANNER/build --data-urlencode json=\'{"parameter": [{"name":"INPUT", "value":"/var/planner/2018/12/planner_1337.xml"}]}\' 2>&1',
                $output,
                $result
            )
            ->shouldBeCalled()
        ;

        $plannerJob->setFile($file->reveal())->shouldBeCalled();
        $plannerJob->setStarted()->shouldBeCalled();
        $this->plannerJobRepository->set($plannerJob)->shouldBeCalled();

        $this->exportHandler->handle(new Export(1337, 'fr', 'admin@vimeet.events', true, $solutionType, true, 42));
    }

    public function test_mode_manual_export()
    {
        $solutionType = 'moving_allowed';

        $plannerJob = $this->prophesize(PlannerJob::class);
        $this->plannerJobRepository->getById(42)->willReturn($plannerJob->reveal());

        $this->dispatcherHandler
            ->handle(new Dispatcher($this->event->reveal()))
            ->shouldBeCalled()
        ;

        $plannerView = $this->prophesize(PlannerView::class);
        $this->plannerHandler
            ->handle(new PlannerViewQuery($this->event->reveal(), 'fr', $solutionType))
            ->shouldBeCalled()
            ->willReturn($plannerView->reveal())
        ;

        $serializedPlanner = 'Serialized planner';
        $this->serializer
            ->serialize($plannerView->reveal(), 'xml', ['xml_root_node_name' => 'MeetingSchedule'])
            ->shouldBeCalled()
            ->willReturn($serializedPlanner)
        ;

        $this->lockMeetingRequestHandler
            ->handle(new LockMeetingRequestUpdate($this->event->reveal(), true))
            ->shouldNotBeCalled()
        ;

        $fileFullPath = '/path/export/2018/12/planner_1337.xml';
        $this->fileStorageAdapter
            ->create($serializedPlanner, 'planner_1337.xml', $this->exportLocationDirectoryPath)
            ->shouldBeCalled()
            ->willReturn($fileFullPath)
        ;

        $fileHash = '75c173410bb63583a00bd63edb92c4acb623482b9ff506c2dffdcbd22243b0be';
        $file = $this->prophesize(File::class);
        $file->getId()->shouldBeCalled()->willReturn(65536);
        $file->getHash()->shouldBeCalled()->willReturn($fileHash);
        $this->fileFactory
            ->createAndPersistFile($fileFullPath)
            ->shouldBeCalled()
            ->willReturn($file->reveal())
        ;

        $this->mailer
            ->send(
                new ExportPlannerMail(
                    $this->event->reveal(),
                    $this->mailSender,
                    'admin@vimeet.events',
                    'fr',
                    $fileHash,
                    65536
                )
            )
            ->shouldBeCalled()
        ;

        $this->exportHandler->handle(new Export(1337, 'fr', 'admin@vimeet.events', false, $solutionType, false, 42));
    }
}
