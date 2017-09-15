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
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
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

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var MailerInterface */
    private $mailer;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var string */
    private $mailSender;

    /**
     * ExportHandler constructor.
     * @param LockMeetingRequestUpdateHandler $lockMeetingRequestHandler
     * @param DispatcherHandler               $dispatcherHandler
     * @param PlannerViewQueryHandler         $plannerHandler
     * @param SerializerAdapterInterface      $serializer
     * @param LocalFileStorageAdapter         $fileStorageAdapter
     * @param string                          $exportLocationDirectoryPath
     * @param EventRepositoryInterface        $eventRepository
     * @param FileRepositoryInterface         $fileRepository
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
        $exportLocationDirectoryPath,
        EventRepositoryInterface $eventRepository,
        FileRepositoryInterface $fileRepository,
        MailerInterface $mailer,
        \DateTimeInterface $dateTime,
        $mailSender
    ) {
        $this->lockMeetingRequestHandler    = $lockMeetingRequestHandler;
        $this->dispatcherHandler            = $dispatcherHandler;
        $this->plannerHandler               = $plannerHandler;
        $this->serializer                   = $serializer;
        $this->fileStorageAdapter           = $fileStorageAdapter;
        $this->exportLocationDirectoryPath  = $exportLocationDirectoryPath;
        $this->eventRepository              = $eventRepository;
        $this->fileRepository               = $fileRepository;
        $this->mailer                       = $mailer;
        $this->dateTime                     = $dateTime;
        $this->mailSender                   = $mailSender;
    }

    /**
     * @param Export $export
     *
     * @throws InvalidArgumentForExportException
     */
    public function handle(Export $export)
    {
        $content = null;
        $event   = $this->eventRepository->getById($export->eventId);

        if ($event === null) {
            throw new InvalidArgumentForExportException(sprintf('Event %s not found', $export->eventId));
        }

        try {
            $this->dispatcherHandler->handle(new Dispatcher($event));
        } catch (UnableToDispatchException $exception) {
            $this->notifyError(sprintf('flash.%s', $exception->indication), $event, $export);

            return;
        }

        if (true === $export->lockMeetingRequest) {
            $this->lockMeetingRequestHandler->handle(new LockMeetingRequestUpdate($event, true));
        }

        try {
            $planner = $this->plannerHandler->handle(new PlannerViewQuery($event, $export->locale, $export->solutionType));
            $content = $this->serializer->serialize($planner, 'xml', ['xml_root_node_name' => self::XML_ROOT_NODE]);
        } catch (SlotNotConfiguredException $exception) {
            $this->notifyError(sprintf('flash.%s', $exception->getMessage()), $event, $export);

            return;
        } catch (DayNotConfiguredException $exception) {
            $this->notifyError(sprintf('flash.%s', $exception->getMessage()), $event, $export);

            return;
        }

        $file = $this->createFile($event, $content);

        $this->notifyCreationOfFile($event, $export, $file);
    }

    /**
     * @param Event  $event
     * @param string $data
     *
     * @return File
     */
    private function createFile(Event $event, &$data)
    {
        $filePath = $this->fileStorageAdapter->create(
            $data,
            sprintf('planner_%s.xml', $event->getId()),
            $this->exportLocationDirectoryPath
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
     * @param string $message
     * @param Event  $event
     * @param Export $command
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
}
