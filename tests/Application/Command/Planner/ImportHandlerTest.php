<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Planner;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\EntityManagerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Command\Meeting\Admin\SpotReassign;
use Proximum\Vimeet\Application\Command\Meeting\Admin\SpotReassignHandler;
use Proximum\Vimeet\Application\Command\Planner\Import;
use Proximum\Vimeet\Application\Command\Planner\ImportHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Model\PlannerJob;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PlannerJobRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\SerializerAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Denormalizer\Planner\PlannerDenormalizer;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\ImportPlannerMail;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ImportHandlerTest extends TestCase
{
    /** @var SerializerAdapterInterface */
    private $serializer;

    /** @var ObjectProphecy */
    private $meetingRepository;

    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $requestRepository;

    /** @var ObjectProphecy */
    private $slotRepository;

    /** @var ObjectProphecy */
    private $spotRepository;

    /** @var ObjectProphecy */
    private $spotReassignHandler;

    /** @var ObjectProphecy */
    private $plannerJobRepository;

    /** @var ObjectProphecy */
    private $entityManagerAdapter;

    /** @var ObjectProphecy */
    private $mailer;

    /** @var ObjectProphecy */
    private $fileStorage;

    /** @var ObjectProphecy */
    private $jobQueue;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var string */
    private $importDirectoryPath;

    /** @var string */
    private $plannerFilesPath;

    /** @var string */
    private $mailSender;

    /** @var ImportHandler */
    private $importHandler;

    public function setUp()
    {
        $this->serializer = new SerializerAdapter(
            new Serializer(
                [
                    new PlannerDenormalizer(),
                    new ObjectNormalizer(),
                ],
                [
                    new XmlEncoder(),
                ]
            )
        );

        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $this->slotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $this->spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $this->spotReassignHandler = $this->prophesize(SpotReassignHandler::class);
        $this->plannerJobRepository = $this->prophesize(PlannerJobRepositoryInterface::class);
        $this->entityManagerAdapter = $this->prophesize(EntityManagerAdapterInterface::class);
        $this->mailer = $this->prophesize(MailerInterface::class);
        $this->fileStorage = $this->prophesize(FileStorageInterface::class);
        $this->jobQueue = $this->prophesize(JobQueueInterface::class);
        $this->dateTime = new \DateTime();
        $this->importDirectoryPath = '/uploads/import_planner/';
        $this->plannerFilesPath = 'tests/Application/Command/Planner/';
        $this->mailSender = 'admin@vimeet.events';

        $this->importHandler = new ImportHandler(
            $this->serializer,
            $this->meetingRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->requestRepository->reveal(),
            $this->slotRepository->reveal(),
            $this->spotRepository->reveal(),
            $this->spotReassignHandler->reveal(),
            $this->plannerJobRepository->reveal(),
            $this->entityManagerAdapter->reveal(),
            $this->mailer->reveal(),
            $this->fileStorage->reveal(),
            $this->jobQueue->reveal(),
            $this->dateTime,
            $this->importDirectoryPath,
            $this->plannerFilesPath,
            $this->mailSender
        );
    }

    public function testHandle()
    {
        $emailToNotify = 'contact@vimeet.events';
        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(42);

        $file = $this->prophesize(File::class);
        $file->getPath()->willReturn('Fixtures/planner_2018_11_30-solved.xml');

        $this->meetingRepository
            ->deleteAll($event->reveal())
            ->shouldBeCalled()
        ;

        $this->requestRepository
            ->getAllAcceptedByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;
        $this->sheetRepository
            ->getByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;
        $this->slotRepository
            ->findByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;
        $this->spotRepository
            ->getActiveByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $plannerJobFile = $this->prophesize(File::class);
        $plannerJobFile->getPath()->shouldBeCalled()->willReturn('planner/file.xml');
        $plannerJob = $this->prophesize(PlannerJob::class);
        $plannerJob->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $plannerJob->getFile()->shouldBeCalled()->willReturn($plannerJobFile->reveal());
        $this->plannerJobRepository
            ->getById(1337)
            ->shouldBeCalled()
            ->willReturn($plannerJob->reveal())
        ;
        $plannerJob->setCompleted()->shouldBeCalled();
        $this->plannerJobRepository->set($plannerJob->reveal())->shouldBeCalled();

        $this->spotReassignHandler
            ->handle(new SpotReassign($event->reveal()))
            ->shouldBeCalled()
        ;

        $this->mailer
            ->send(new ImportPlannerMail($event->reveal(), $this->mailSender, $emailToNotify, 'fr'))
            ->shouldBeCalled()
        ;

        $this->jobQueue->generateMeetingSolutionAnalytic($event->reveal())->shouldBeCalled();
        $this->jobQueue->indexInCatalogSheetsByEvent($event->reveal())->shouldBeCalled();
        $this->jobQueue->aggregateParticipantAssignedToRequest($event->reveal())->shouldBeCalled();
        $this->jobQueue->aggregateEventUsersFullUnavailability($event->reveal(), true)->shouldBeCalled();
        $this->jobQueue->aggregateAvailableSlot($event->reveal())->shouldBeCalled();
        $this->jobQueue->aggregatePhoneValidationStatus($event->reveal())->shouldBeCalled();

        $this->fileStorage
            ->remove('tests/Application/Command/Planner/Fixtures/planner_2018_11_30-solved.xml', true)
            ->shouldBeCalled()
        ;

        $this->fileStorage
            ->remove('tests/Application/Command/Planner/planner/file.xml', true)
            ->shouldBeCalled()
        ;

        $this->importHandler->handle(new Import($file->reveal(), $event->reveal(), $emailToNotify, 'fr', 1337));
    }
}
