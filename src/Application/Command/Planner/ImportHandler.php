<?php

namespace Proximum\Vimeet\Application\Command\Planner;

use Proximum\Vimeet\Application\Adapter\EntityManagerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Command\Meeting\Admin\SpotReassign;
use Proximum\Vimeet\Application\Command\Meeting\Admin\SpotReassignHandler;
use Proximum\Vimeet\Application\Exception\Planner\Import\InvalidArgumentForImportException;
use Proximum\Vimeet\Application\Exception\Planner\InvalidXmlException;
use Proximum\Vimeet\Application\View\Planner\Result\MeetingResult;
use Proximum\Vimeet\Application\View\Planner\Result\PlannerResult;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PlannerJobRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\ParticipantFinder;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\ImportPlannerMail;

class ImportHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var SerializerAdapterInterface */
    private $serializer;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var MeetingSlotRepositoryInterface */
    private $slotRepository;

    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var SpotReassignHandler */
    private $spotReassignHandler;

    /** @var EntityManagerAdapterInterface */
    private $entityManagerAdapter;

    /** @var MailerInterface */
    private $mailer;

    /** @var FileStorageInterface */
    private $fileStorage;

    /** @var string */
    private $importDirectoryPath;

    /** @var string */
    private $mailSender;

    /** @var Request[] */
    private $requests = [];

    /** @var Sheet[] */
    private $sheets = [];

    /** @var MeetingSlot[] */
    private $slots = [];

    /** @var Spot[] */
    private $spots = [];

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var JobQueueInterface */
    private $jobQueue;

    /** @var string */
    private $plannerFilesPath;

    /** @var PlannerJobRepositoryInterface */
    private $plannerJobRepository;

    /**
     * @param SerializerAdapterInterface     $serializer
     * @param MeetingRepositoryInterface     $meetingRepository
     * @param SheetRepositoryInterface       $sheetRepository
     * @param RequestRepositoryInterface     $requestRepository
     * @param MeetingSlotRepositoryInterface $slotRepository
     * @param SpotRepositoryInterface        $spotRepository
     * @param SpotReassignHandler            $spotReassignHandler
     * @param PlannerJobRepositoryInterface  $plannerJobRepository
     * @param EntityManagerAdapterInterface  $entityManagerAdapter
     * @param MailerInterface                $mailer
     * @param FileStorageInterface           $fileStorage
     * @param JobQueueInterface              $jobQueue
     * @param \DateTimeInterface             $dateTime
     * @param string                         $importDirectoryPath
     * @param string                         $plannerFilesPath
     * @param string                         $mailSender
     */
    public function __construct(
        SerializerAdapterInterface $serializer,
        MeetingRepositoryInterface $meetingRepository,
        SheetRepositoryInterface $sheetRepository,
        RequestRepositoryInterface $requestRepository,
        MeetingSlotRepositoryInterface $slotRepository,
        SpotRepositoryInterface $spotRepository,
        SpotReassignHandler $spotReassignHandler,
        PlannerJobRepositoryInterface $plannerJobRepository,
        EntityManagerAdapterInterface $entityManagerAdapter,
        MailerInterface $mailer,
        FileStorageInterface $fileStorage,
        JobQueueInterface $jobQueue,
        \DateTimeInterface $dateTime,
        string $importDirectoryPath,
        string $plannerFilesPath,
        string $mailSender
    ) {
        $this->serializer           = $serializer;
        $this->meetingRepository    = $meetingRepository;
        $this->sheetRepository      = $sheetRepository;
        $this->requestRepository    = $requestRepository;
        $this->slotRepository       = $slotRepository;
        $this->spotRepository       = $spotRepository;
        $this->spotReassignHandler  = $spotReassignHandler;
        $this->plannerJobRepository = $plannerJobRepository;
        $this->entityManagerAdapter = $entityManagerAdapter;
        $this->mailer               = $mailer;
        $this->fileStorage          = $fileStorage;
        $this->jobQueue             = $jobQueue;
        $this->dateTime             = $dateTime;
        $this->importDirectoryPath  = $importDirectoryPath;
        $this->plannerFilesPath     = $plannerFilesPath;
        $this->mailSender           = $mailSender;
    }

    /**
     * @param Import $import
     *
     * @throws InvalidArgumentForImportException
     * @throws InvalidXmlException
     */
    public function handle(Import $import)
    {
        // Remove all meeting of the event
        $this->meetingRepository->deleteAll($import->event);

        $plannerJob = null;

        if ($import->plannerJobId > 0) {
            $plannerJob = $this->plannerJobRepository->getById($import->plannerJobId);

            if ($import->event->getId() !== $plannerJob->getEvent()->getId()) {
                throw new \InvalidArgumentException('Given PlannerJob not in this event');
            }
        }

        $importDirectory = $this->importDirectoryPath;

        if (null !== $plannerJob) {
            $importDirectory = $this->plannerFilesPath;
        }

        $content = file_get_contents($importDirectory . $import->file->getPath());

        try {
            /** @var PlannerResult $plannerResult */
            $plannerResult = $this->serializer->deserialize($content, PlannerResult::class, 'xml');
        } catch (\InvalidArgumentException $exception) {
            throw new InvalidXmlException();
        }

        $this->handlePlannerResult($import->event, $plannerResult);
        $this->spotReassignHandler->handle(new SpotReassign($import->event));

        $this->notifyAboutImportSuccess($import->event, $import);

        if (null !== $plannerJob) {
            // Due to entityManager clear() in handlePlannerResult(),
            // we need to retrieve plannerJob entity before updating it
            $plannerJob = $this->plannerJobRepository->getById($import->plannerJobId);
            $plannerJob->setCompleted();
            $this->plannerJobRepository->set($plannerJob);
        }

        $this->jobQueue->indexInCatalogSheetsByEvent($import->event);
        $this->jobQueue->aggregateParticipantAssignedToRequest($import->event);
        $this->jobQueue->aggregateEventUsersFullUnavailability($import->event, true);
        $this->jobQueue->aggregateAvailableSlot($import->event);
        $this->jobQueue->aggregatePhoneValidationStatus($import->event);

        $this->generateMeetingSolutionAnalytic($import->event);

        $this->fileStorage->remove($importDirectory . $import->file->getPath(), true);

        if (null !== $plannerJob) {
            $this->fileStorage->remove($importDirectory . $plannerJob->getFile()->getPath(), true);
        }
    }

    /**
     * @param Event         $event
     * @param PlannerResult $plannerResult
     */
    private function handlePlannerResult(Event $event, PlannerResult $plannerResult)
    {
        $this->prepareRequestSheetSlotAndSpot($event);
        $toFlush = [];

        $index = 1;
        foreach ($plannerResult->meetings as $meetingResult) {
            $meeting = $this->handleMeeting($event, $meetingResult);

            if (null !== $meeting) {
                $toFlush[] = $meeting;
            }

            // Each 1000 meetings, flush and clear to optimize the insertion
            if (0 === ($index % 1000)) {
                $this->entityManagerAdapter->flush($toFlush);

                /** @var Meeting $flush */
                foreach ($toFlush as $flush) {
                    $this->entityManagerAdapter->detach($flush);
                }

                $toFlush = [];
            }

            ++$index;
        }

        $this->entityManagerAdapter->flush();
        $this->entityManagerAdapter->clear();
    }

    /**
     * @param Event $event
     */
    private function prepareRequestSheetSlotAndSpot(Event $event)
    {
        $requests = $this->requestRepository->getAllAcceptedByEvent($event);
        $sheets   = $this->sheetRepository->getByEvent($event);
        $slots    = $this->slotRepository->findByEvent($event);
        $spots    = $this->spotRepository->getActiveByEvent($event);

        foreach ($requests as $request) {
            $this->requests[$request->getId()] = $request;
        }

        foreach ($sheets as $sheet) {
            $this->sheets[$sheet->getId()] = $sheet;
        }

        foreach ($slots as $slot) {
            $this->slots[$slot->getId()] = $slot;
        }

        foreach ($spots as $spot) {
            $this->spots[$spot->getId()] = $spot;
        }
    }

    /**
     * @param Event         $event
     * @param MeetingResult $meetingResult
     *
     * @return null|Meeting
     */
    private function handleMeeting(Event $event, MeetingResult $meetingResult): ?Meeting
    {
        if (!isset($this->sheets[$meetingResult->sheetFrom->id])
            || !isset($this->sheets[$meetingResult->sheetTo->id])
            || !isset($this->requests[$meetingResult->requestId])
            || null === $meetingResult->spot
            || !isset($this->spots[$meetingResult->spot->id])
            || null === $meetingResult->slot
            || !isset($this->slots[$meetingResult->slot->id])
        ) {
            return null; // Early return if element to create the meeting are not present in the db
        }

        $request   = $this->requests[$meetingResult->requestId];
        $sheetFrom = $this->sheets[$meetingResult->sheetFrom->id];
        $sheetTo   = $this->sheets[$meetingResult->sheetTo->id];
        $spot      = $this->spots[$meetingResult->spot->id];
        $slot      = $this->slots[$meetingResult->slot->id];
        $participantsFrom = [];
        $participantsTo   = [];

        // Check if participant are present also
        foreach ($meetingResult->userResults as $userResult) {
            $participantFrom = ParticipantFinder::getParticipantWithUserId($sheetFrom, (int) $userResult->id);
            $participantTo   = ParticipantFinder::getParticipantWithUserId($sheetTo, (int) $userResult->id);

            if (null !== $participantFrom) {
                $participantsFrom[] = $participantFrom;
            }

            if (null !== $participantTo) {
                $participantsTo[] = $participantTo;
            }
        }

        if (empty($participantsTo) || empty($participantsFrom)) {
            return null;
        }

        $meeting = new Meeting(
            $request,
            $slot,
            $sheetFrom,
            $participantsFrom,
            $sheetTo,
            $participantsTo,
            $this->dateTime,
            $spot,
            $event,
            $meetingResult->isBlockedSpot,
            $meetingResult->isBlockedSlot,
            Meeting::CREATED_BY_PLANNER
        );

        $this->entityManagerAdapter->persist($meeting);

        return $meeting;
    }

    /**
     * Send a mail to current admin to notify him about successful planner import
     *
     * @param Event  $event
     * @param Import $command
     */
    private function notifyAboutImportSuccess(Event $event, Import $command)
    {
        $this->mailer->send(new ImportPlannerMail(
            $event,
            $this->mailSender,
            $command->emailToNotify,
            $command->locale
        ));
    }

    /**
     * @param Event $event
     */
    private function generateMeetingSolutionAnalytic(Event $event)
    {
        $this->jobQueue->generateMeetingSolutionAnalytic($event);
    }
}
