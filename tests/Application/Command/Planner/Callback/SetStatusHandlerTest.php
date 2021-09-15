<?php

namespace Proximum\Vimeet\Tests\Application\Command\Payment;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Planner\Callback\SetStatus;
use Proximum\Vimeet\Application\Command\Planner\Callback\SetStatusHandler;
use Proximum\Vimeet\Domain\KeyDates\Checker\EventOpenAccessChecker;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Model\PlannerJob;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PlannerJobRepositoryInterface;

class SetStatusHandlerTest extends TestCase
{
    public function testEventIsAlreadyOpened()
    {
        $dateTime = new \DateTime();
        $plannerTrustedName = 'VIMEET_PLANNER';
        $plannerFilesPath = 'path/to/files/';

        $event = $this->prophesize(Event::class);

        $plannerJob = $this->prophesize(PlannerJob::class);
        $plannerJob->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $plannerJob->setError('flash.admin.planner.export.eventIsAlreadyOpened')->shouldBeCalled();

        $plannerJobRepository = $this->prophesize(PlannerJobRepositoryInterface::class);
        $plannerJobRepository
            ->findByFilename('planner_file_1234.xml')
            ->shouldBeCalled()
            ->willReturn($plannerJob->reveal())
        ;
        $plannerJobRepository->set(Argument::type(PlannerJob::class))->shouldBeCalled();

        $eventOpenAccessChecker = $this->prophesize(EventOpenAccessChecker::class);
        $eventOpenAccessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->importPlannerForEvent()->shouldNotBeCalled();

        $fileRepository = $this->prophesize(FileRepositoryInterface::class);
        $fileRepository->add()->shouldNotBeCalled();

        $fileSystemAdapter = $this->prophesize(FileSystemAdapterInterface::class);

        $setStatusHandler = new SetStatusHandler(
            $plannerJobRepository->reveal(),
            $fileRepository->reveal(),
            $plannerTrustedName,
            $jobQueue->reveal(),
            $plannerFilesPath,
            $eventOpenAccessChecker->reveal(),
            $fileSystemAdapter->reveal(),
            $dateTime
        );

        $status = '{
            "name": "VIMEET_PLANNER",
            "display_name": "VIMEET_PLANNER",
            "build": {
                "phase": "FINALIZED",
                "status": "SUCCESS",
                "parameters": {
                    "INPUT": "planner_file_1234.xml"
                }
            }
        }';

        $setStatusHandler->handle(new SetStatus(json_decode($status, true)));
    }

    public function testIsPhaseCompletedButDoNothing()
    {
        $dateTime = new \DateTime();
        $plannerTrustedName = 'VIMEET_PLANNER';
        $plannerFilesPath = 'path/to/files/';

        $plannerJob = $this->prophesize(PlannerJob::class);

        $plannerJobRepository = $this->prophesize(PlannerJobRepositoryInterface::class);
        $plannerJobRepository
            ->findByFilename('planner_file_1234.xml')
            ->shouldBeCalled()
            ->willReturn($plannerJob->reveal())
        ;
        $plannerJobRepository->set()->shouldNotBeCalled();

        $eventOpenAccessChecker = $this->prophesize(EventOpenAccessChecker::class);
        $eventOpenAccessChecker->allowedToAccess()->shouldNotBeCalled();

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->importPlannerForEvent()->shouldNotBeCalled();

        $fileRepository = $this->prophesize(FileRepositoryInterface::class);
        $fileRepository->add()->shouldNotBeCalled();

        $fileSystemAdapter = $this->prophesize(FileSystemAdapterInterface::class);

        $setStatusHandler = new SetStatusHandler(
            $plannerJobRepository->reveal(),
            $fileRepository->reveal(),
            $plannerTrustedName,
            $jobQueue->reveal(),
            $plannerFilesPath,
            $eventOpenAccessChecker->reveal(),
            $fileSystemAdapter->reveal(),
            $dateTime
        );

        $status = '{
            "name": "VIMEET_PLANNER",
            "display_name": "VIMEET_PLANNER",
            "build": {
                "phase": "COMPLETED",
                "status": "SUCCESS",
                "parameters": {
                    "INPUT": "planner_file_1234.xml"
                }
            }
        }';

        $setStatusHandler->handle(new SetStatus(json_decode($status, true)));
    }

    public function testFileNotFound()
    {
        $this->expectException(\InvalidArgumentException::class);

        $dateTime = new \DateTime();
        $plannerTrustedName = 'VIMEET_PLANNER';
        $plannerFilesPath = 'path/to/files/';

        $event = $this->prophesize(Event::class);

        $plannerJobRepository = $this->prophesize(PlannerJobRepositoryInterface::class);
        $plannerJobRepository
            ->findByFilename('planner_file_1234.xml')
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $plannerJobRepository->set(Argument::type(PlannerJob::class))->shouldNotBeCalled();

        $eventOpenAccessChecker = $this->prophesize(EventOpenAccessChecker::class);
        $eventOpenAccessChecker->allowedToAccess($event->reveal())->shouldNotBeCalled();

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->importPlannerForEvent()->shouldNotBeCalled();

        $fileRepository = $this->prophesize(FileRepositoryInterface::class);
        $fileRepository->add()->shouldNotBeCalled();

        $fileSystemAdapter = $this->prophesize(FileSystemAdapterInterface::class);

        $setStatusHandler = new SetStatusHandler(
            $plannerJobRepository->reveal(),
            $fileRepository->reveal(),
            $plannerTrustedName,
            $jobQueue->reveal(),
            $plannerFilesPath,
            $eventOpenAccessChecker->reveal(),
            $fileSystemAdapter->reveal(),
            $dateTime
        );

        $status = '{
            "name": "VIMEET_PLANNER",
            "display_name": "VIMEET_PLANNER",
            "build": {
                "phase": "FINALIZED",
                "status": "SUCCESS",
                "parameters": {
                    "INPUT": "planner_file_1234.xml"
                }
            }
        }';

        $setStatusHandler->handle(new SetStatus(json_decode($status, true)));
    }

    public function testSuccessHandle()
    {
        $dateTime = new \DateTime();
        $plannerTrustedName = 'VIMEET_PLANNER';
        $plannerFilesPath = 'path/to/files/';

        $event = $this->prophesize(Event::class);
        $admin = $this->prophesize(Admin::class);
        $admin->getLocale()->shouldBeCalled()->willReturn('fr');

        $plannerJob = $this->prophesize(PlannerJob::class);
        $plannerJob->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $plannerJob->setSuccess()->shouldBeCalled();
        $plannerJob->getAdmin()->shouldBeCalled()->willReturn($admin->reveal());
        $plannerJob->getFile()->willReturn(new File('planner_file_1234.xml', $dateTime));

        $plannerJobRepository = $this->prophesize(PlannerJobRepositoryInterface::class);
        $plannerJobRepository
            ->findByFilename('planner_file_1234.xml')
            ->shouldBeCalled()
            ->willReturn($plannerJob->reveal())
        ;
        $plannerJobRepository->set(Argument::type(PlannerJob::class))->shouldBeCalled();

        $eventOpenAccessChecker = $this->prophesize(EventOpenAccessChecker::class);
        $eventOpenAccessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(false);

        $expectedFile = new File('planner_file_1234-solved.xml', $dateTime);

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue
            ->importPlannerForEvent(
                $expectedFile,
                $event->reveal(),
                $admin->reveal(),
                'fr',
                $plannerJob->reveal()
            )
            ->shouldBeCalled()
        ;

        $fileRepository = $this->prophesize(FileRepositoryInterface::class);
        $fileRepository
            ->add($expectedFile)
            ->shouldBeCalled()
        ;

        $fileSystemAdapter = $this->prophesize(FileSystemAdapterInterface::class);
        $fileSystemAdapter
            ->exists('path/to/files/planner_file_1234-solved.xml')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $setStatusHandler = new SetStatusHandler(
            $plannerJobRepository->reveal(),
            $fileRepository->reveal(),
            $plannerTrustedName,
            $jobQueue->reveal(),
            $plannerFilesPath,
            $eventOpenAccessChecker->reveal(),
            $fileSystemAdapter->reveal(),
            $dateTime
        );

        $status = '{
            "name": "VIMEET_PLANNER",
            "display_name": "VIMEET_PLANNER",
            "build": {
                "phase": "FINALIZED",
                "status": "SUCCESS",
                "parameters": {
                    "INPUT": "planner_file_1234.xml"
                }
            }
        }';

        $setStatusHandler->handle(new SetStatus(json_decode($status, true)));
    }

    public function testSolvedFileNotFoundHandle()
    {
        $dateTime = new \DateTime();
        $plannerTrustedName = 'VIMEET_PLANNER';
        $plannerFilesPath = 'path/to/files/';

        $event = $this->prophesize(Event::class);

        $plannerJob = $this->prophesize(PlannerJob::class);
        $plannerJob->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $plannerJob->getFile()->shouldBeCalled()->willReturn(new File('planner_file_1234.xml', $dateTime));
        $plannerJob->setError('flash.admin.planner.export.solvedFileNotFound')->shouldBeCalled();

        $plannerJobRepository = $this->prophesize(PlannerJobRepositoryInterface::class);
        $plannerJobRepository
            ->findByFilename('planner_file_1234.xml')
            ->shouldBeCalled()
            ->willReturn($plannerJob->reveal())
        ;
        $plannerJobRepository->set(Argument::type(PlannerJob::class))->shouldBeCalled();

        $eventOpenAccessChecker = $this->prophesize(EventOpenAccessChecker::class);
        $eventOpenAccessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(false);

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->importPlannerForEvent()->shouldNotBeCalled();

        $fileRepository = $this->prophesize(FileRepositoryInterface::class);
        $fileRepository->add()->shouldNotBeCalled();

        $fileSystemAdapter = $this->prophesize(FileSystemAdapterInterface::class);
        $fileSystemAdapter
            ->exists('path/to/files/planner_file_1234-solved.xml')
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $setStatusHandler = new SetStatusHandler(
            $plannerJobRepository->reveal(),
            $fileRepository->reveal(),
            $plannerTrustedName,
            $jobQueue->reveal(),
            $plannerFilesPath,
            $eventOpenAccessChecker->reveal(),
            $fileSystemAdapter->reveal(),
            $dateTime
        );

        $status = '{
            "name": "VIMEET_PLANNER",
            "display_name": "VIMEET_PLANNER",
            "build": {
                "phase": "FINALIZED",
                "status": "SUCCESS",
                "parameters": {
                    "INPUT": "planner_file_1234.xml"
                }
            }
        }';

        $setStatusHandler->handle(new SetStatus(json_decode($status, true)));
    }

    public function testStatusFailure()
    {
        $dateTime = new \DateTime();
        $plannerTrustedName = 'VIMEET_PLANNER';
        $plannerFilesPath = 'path/to/files/';

        $plannerJob = $this->prophesize(PlannerJob::class);
        $plannerJob->setError('flash.admin.planner.export.plannerError')->shouldBeCalled();

        $plannerJobRepository = $this->prophesize(PlannerJobRepositoryInterface::class);
        $plannerJobRepository
            ->findByFilename('planner_file_1234.xml')
            ->shouldBeCalled()
            ->willReturn($plannerJob->reveal())
        ;
        $plannerJobRepository->set(Argument::type(PlannerJob::class))->shouldBeCalled();

        $eventOpenAccessChecker = $this->prophesize(EventOpenAccessChecker::class);
        $eventOpenAccessChecker->allowedToAccess()->shouldNotBeCalled();

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->importPlannerForEvent()->shouldNotBeCalled();

        $fileRepository = $this->prophesize(FileRepositoryInterface::class);
        $fileRepository->add()->shouldNotBeCalled();

        $fileSystemAdapter = $this->prophesize(FileSystemAdapterInterface::class);

        $setStatusHandler = new SetStatusHandler(
            $plannerJobRepository->reveal(),
            $fileRepository->reveal(),
            $plannerTrustedName,
            $jobQueue->reveal(),
            $plannerFilesPath,
            $eventOpenAccessChecker->reveal(),
            $fileSystemAdapter->reveal(),
            $dateTime
        );

        $status = '{
            "name": "VIMEET_PLANNER",
            "display_name": "VIMEET_PLANNER",
            "build": {
                "phase": "FINALIZED",
                "status": "FAILURE",
                "parameters": {
                    "INPUT": "planner_file_1234.xml"
                }
            }
        }';

        $setStatusHandler->handle(new SetStatus(json_decode($status, true)));
    }
}
