<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Planner;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Command\MeetingRequest\Admin\LockMeetingRequestUpdate;
use Proximum\Vimeet\Application\Command\MeetingRequest\Admin\LockMeetingRequestUpdateHandler;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\Dispatcher;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\DispatcherHandler;
use Proximum\Vimeet\Application\Exception\Order\Export\InvalidArgumentForExportException;
use Proximum\Vimeet\Application\Exception\Planner\DayNotConfiguredException;
use Proximum\Vimeet\Application\Exception\Planner\SlotNotConfiguredException;
use Proximum\Vimeet\Application\Query\Planner\PlannerViewQuery;
use Proximum\Vimeet\Application\Query\Planner\PlannerViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Model\PlannerJob;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PlannerJobRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\Exception\UnableToDispatchException;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\Error\ExportPlannerMailError;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\ExportPlannerMail;

class ExportHandler
{
    const XML_ROOT_NODE = 'MeetingSchedule';

    /** @var LockMeetingRequestUpdateHandler */
    private $lockMeetingRequestHandler;

    /** @var DispatcherHandler */
    private $dispatcherHandler;

    /** @var PlannerViewQueryHandler */
    private $plannerHandler;

    /** @var SerializerAdapterInterface */
    private $serializer;

    /** @var LocalFileStorageAdapter */
    private $fileStorageAdapter;

    /** @var string */
    private $exportLocationDirectoryPath;

    /** @var string */
    private $plannerFilesPath;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var PlannerJobRepositoryInterface */
    private $plannerJobRepository;

    /** @var MailerInterface */
    private $mailer;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var string */
    private $mailSender;

    /**
     * ExportHandler constructor.
     *
     * @param LockMeetingRequestUpdateHandler $lockMeetingRequestHandler
     * @param DispatcherHandler               $dispatcherHandler
     * @param PlannerViewQueryHandler         $plannerHandler
     * @param SerializerAdapterInterface      $serializer
     * @param LocalFileStorageAdapter         $fileStorageAdapter
     * @param string                          $exportLocationDirectoryPath
     * @param string                          $plannerFilesPath
     * @param EventRepositoryInterface        $eventRepository
     * @param FileRepositoryInterface         $fileRepository
     * @param PlannerJobRepositoryInterface   $plannerJobRepository
     * @param MailerInterface                 $mailer
     * @param \DateTimeInterface              $dateTime
     * @param string                          $mailSender
     */
    public function __construct(
        LockMeetingRequestUpdateHandler $lockMeetingRequestHandler,
        DispatcherHandler $dispatcherHandler,
        PlannerViewQueryHandler $plannerHandler,
        SerializerAdapterInterface $serializer,
        LocalFileStorageAdapter $fileStorageAdapter,
        string $exportLocationDirectoryPath,
        string $plannerFilesPath,
        EventRepositoryInterface $eventRepository,
        FileRepositoryInterface $fileRepository,
        PlannerJobRepositoryInterface $plannerJobRepository,
        MailerInterface $mailer,
        \DateTimeInterface $dateTime,
        $mailSender
    ) {
        $this->lockMeetingRequestHandler   = $lockMeetingRequestHandler;
        $this->dispatcherHandler           = $dispatcherHandler;
        $this->plannerHandler              = $plannerHandler;
        $this->serializer                  = $serializer;
        $this->fileStorageAdapter          = $fileStorageAdapter;
        $this->exportLocationDirectoryPath = $exportLocationDirectoryPath;
        $this->plannerFilesPath            = $plannerFilesPath;
        $this->eventRepository             = $eventRepository;
        $this->fileRepository              = $fileRepository;
        $this->plannerJobRepository        = $plannerJobRepository;
        $this->mailer                      = $mailer;
        $this->dateTime                    = $dateTime;
        $this->mailSender                  = $mailSender;
    }

    /**
     * @param Export $export
     *
     * @throws InvalidArgumentForExportException
     */
    public function handle(Export $export)
    {
        $event = $this->eventRepository->getById($export->eventId);

        if (null === $event) {
            throw new InvalidArgumentForExportException(sprintf('Event %s not found', $export->eventId));
        }

        $plannerJob = $this->getPlannerJob($export->plannerJobId);

        try {
            $this->dispatcherHandler->handle(new Dispatcher($event));
        } catch (UnableToDispatchException $exception) {
            $errorKey = sprintf('flash.%s', $exception->getMessage());
            $this->notifyError($errorKey, $event, $export);
            $this->saveErrorInPlannerJob($plannerJob, $errorKey);

            return;
        }

        try {
            $planner = $this->plannerHandler->handle(new PlannerViewQuery($event, $export->locale, $export->solutionType));
            $content = $this->serializer->serialize($planner, 'xml', ['xml_root_node_name' => self::XML_ROOT_NODE]);

            if (true === $export->lockMeetingRequest) {
                $this->lockMeetingRequestHandler->handle(new LockMeetingRequestUpdate($event, true));
            }
        } catch (SlotNotConfiguredException $exception) {
            $errorKey = sprintf('flash.%s', $exception->getMessage());
            $this->saveErrorInPlannerJob($plannerJob, $errorKey);
            $this->notifyError($errorKey, $event, $export, $plannerJob);

            return;
        } catch (DayNotConfiguredException $exception) {
            $errorKey = sprintf('flash.%s', $exception->getMessage());
            $this->saveErrorInPlannerJob($plannerJob, $errorKey);
            $this->notifyError($errorKey, $event, $export, $plannerJob);

            return;
        }

        $path = $export->isModeAuto ? $this->plannerFilesPath : $this->exportLocationDirectoryPath;

        $file = $this->createFile(
            $event,
            $content,
            $path
        );

        if (!$export->isModeAuto) {
            $this->notifyCreationOfFile($event, $export, $file);
        } else {
            $this->saveFileInPlannerJob($plannerJob, $file);
            $this->callPlanner($file, $path);
        }
    }

    /**
     * @param null|PlannerJob $plannerJob
     * @param File            $file
     */
    private function saveFileInPlannerJob(?PlannerJob $plannerJob, File $file): void
    {
        if ($plannerJob instanceof PlannerJob) {
            $plannerJob->setFile($file);
            $this->plannerJobRepository->set($plannerJob);
        }
    }

    /**
     * @param null|PlannerJob $plannerJob
     * @param string          $errorKey
     */
    private function saveErrorInPlannerJob(?PlannerJob $plannerJob, string $errorKey): void
    {
        if ($plannerJob instanceof PlannerJob) {
            $plannerJob->setError($errorKey);
            $this->plannerJobRepository->set($plannerJob);
        }
    }

    /**
     * @param int|null $plannerJobId
     *
     * @return null|PlannerJob
     * @throws InvalidArgumentForExportException
     */
    private function getPlannerJob(?int $plannerJobId): ?PlannerJob
    {
        if (null === $plannerJobId) {
            return null;
        }

        $plannerJob = $this->plannerJobRepository->getById($plannerJobId);

        if (null === $plannerJob) {
            throw new InvalidArgumentForExportException(sprintf('PlannerJob %s not found', $plannerJobId));
        }

        return $plannerJob;
    }

    /**
     * @param Event  $event
     * @param string $data
     * @param string $path
     *
     * @return File
     */
    private function createFile(Event $event, string &$data, string $path): File
    {
        $filePath = $this->fileStorageAdapter->create(
            $data,
            sprintf('planner_%s.xml', $event->getId()),
            $path
        );

        $file = new File($filePath, $this->dateTime);
        $this->fileRepository->add($file);

        return $file;
    }

    /**
     * Send a mail to the emailToNotify with the link to download the xml file
     *
     * @param Event  $event
     * @param Export $command
     * @param File   $file
     */
    private function notifyCreationOfFile(Event $event, Export $command, File $file)
    {
        $this->mailer->send(new ExportPlannerMail(
            $event,
            $this->mailSender,
            $command->emailToNotify,
            $command->locale,
            $file->getHash(),
            $file->getId()
        ));
    }

    /**
     * Send a mail to the emailToNotify with the error message
     *
     * @param string          $message
     * @param Event           $event
     * @param Export          $command
     */
    private function notifyError($message, Event $event, Export $command)
    {
        $this->mailer->send(
            new ExportPlannerMailError(
                $event,
                $this->mailSender,
                $command->emailToNotify,
                $command->locale,
                $message
            )
        );
    }

    private function callPlanner(File $file, string $path): void
    {
        $url = 'http://optaplanner:8080/job/OptaPlanner_PREPROD_run/build';
        $user = 'vimeet:ecfbaffaa67e252544bad999254d21f4';

        $fileFullPath = $path . $file->getPath();
        $json = str_replace('%filename%', $fileFullPath, '{"parameter": [{"name": "INPUT", "value": "%filename%"}]}');
        $urlEncoded = urlencode($url . '?json=' . $json);
        var_dump("\n$urlEncoded\n");

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, $user);
        curl_setopt($curl, CURLOPT_URL, $urlEncoded);

        $result = curl_exec($curl);
        var_dump($result);

        curl_close($curl);
    }
}
