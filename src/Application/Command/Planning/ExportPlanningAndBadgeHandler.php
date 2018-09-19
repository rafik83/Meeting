<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Planning;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Badge\GetUserBadgeAndPlanningByEventQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Planning\PlanningOrderedBy;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\PrintPlanningAndBadgeMail;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class ExportPlanningAndBadgeHandler
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var ParticipantInfoGuesserCache */
    private $participantInfoGuesserCache;

    /** @var EngineInterface */
    private $templating;

    /** @var LocalFileStorageAdapter */
    private $localFileStorageAdapter;

    /** @var string Path to where the planning need to be stored */
    private $printPlanningPath;

    /** @var MailerInterface */
    private $mailer;

    /** @var string */
    private $mailSender;

    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        ParticipantInfoGuesserCache $participantInfoGuesserCache,
        EngineInterface $templating,
        LocalFileStorageAdapter $localFileStorageAdapter,
        MailerInterface $mailer,
        string $printPlanningPath,
        string $mailSender,
        FileRepositoryInterface $fileRepository,
        QueryBusInterface $queryBus,
        \DateTimeInterface $dateTime
    ) {
        $this->participantRepository = $participantRepository;
        $this->participantInfoGuesserCache = $participantInfoGuesserCache;
        $this->templating = $templating;
        $this->localFileStorageAdapter = $localFileStorageAdapter;
        $this->mailer = $mailer;
        $this->printPlanningPath = $printPlanningPath;
        $this->mailSender = $mailSender;
        $this->fileRepository = $fileRepository;
        $this->queryBus = $queryBus;
        $this->dateTime = $dateTime;
    }

    public function handle(ExportPlanningAndBadge $exportPlanningAndBadge): void
    {
        /** @var Participant[] $participants */
        $participants = $this->participantRepository
            ->getParticipantsBySheetIdsWithSheetAndTypeHydrated($exportPlanningAndBadge->sheetIds);

        if (0 === \count($participants)) {
            return;
        }

        /** @var Participant $firstParticipant */
        $firstParticipant = reset($participants);
        $event = $firstParticipant->getSheet()->getEvent();

        $this->orderParticipant($event, $exportPlanningAndBadge->orderBy, $participants);

        $planningsAndBadges = [];

        foreach ($participants as $participant) {
            $planningsAndBadges[] = $this->queryBus->handle(
                new GetUserBadgeAndPlanningByEventQuery(
                    $event,
                    $participant->getUser()
                )
            );
        }

        $print = $this->templating->render('AdminBundle:Planning/Print:planningsAndBadges.html.twig', [
            'planningsAndBadges' => $planningsAndBadges,
        ]);

        $file = $this->createFile($print);
        $this->notify($event, $exportPlanningAndBadge, $file);
    }

    private function createFile(string &$print): File
    {
        $filePath = $this->localFileStorageAdapter->create($print, 'print_planning_and_badge.html', $this->printPlanningPath);

        $file = new File($filePath, $this->dateTime);
        $this->fileRepository->add($file);

        return $file;
    }

    private function notify(Event $event, ExportPlanningAndBadge $exportPlanningAndBadgeAndBadge, File $file): void
    {
        $this->mailer->send(
            new PrintPlanningAndBadgeMail(
                $event,
                $this->mailSender,
                $exportPlanningAndBadgeAndBadge->emailToNotify,
                $exportPlanningAndBadgeAndBadge->locale,
                $file->getHash(),
                $file->getId(),
                $exportPlanningAndBadgeAndBadge->orderBy
            )
        );
    }

    private function orderParticipant(Event $event, $orderBy, array &$participants): void
    {
        // Only ORDER_BY_PARTICIPANT_LAST_NAME accepted for now
        if (PlanningOrderedBy::ORDER_BY_PARTICIPANT_LAST_NAME !== $orderBy) {
            return;
        }

        foreach ($participants as $participant) {
            $this->participantInfoGuesserCache->guessParticipantLastName($participant, $event->getFallback());
        }

        usort($participants, function (Participant $participantLeft, Participant $participantRight) use ($event) {
            $left  = $this->participantInfoGuesserCache->guessParticipantLastName($participantLeft, $event->getFallback());
            $right = $this->participantInfoGuesserCache->guessParticipantLastName($participantRight, $event->getFallback());

            return strcasecmp($left, $right);
        });
    }
}
