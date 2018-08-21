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
use Proximum\Vimeet\Application\Components\Planning\Displayer\ParticipantPlanningDisplayer;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Planning\PlanningOrderedBy;
use Proximum\Vimeet\Domain\Planning\PlanningPrint;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\PrintPlanningMail;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class ExportPlanningHandler
{
    const PLANNING_TEMPLATE = 'AdminBundle:Planning/Print:plannings.html.twig';

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var ParticipantInfoGuesserCache */
    private $participantInfoGuesserCache;

    /** @var SheetInfoGuesserCache */
    private $sheetInfoGuesserCache;

    /** @var ParticipantPlanningDisplayer */
    private $participantPlanningDisplayer;

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

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var TipTranslationViewQueryHandler */
    private $tipTranslationViewQueryHandler;

    /**
     * @param ParticipantRepositoryInterface $participantRepository
     * @param ParticipantInfoGuesserCache    $participantInfoGuesserCache
     * @param SheetInfoGuesserCache          $sheetInfoGuesserCache
     * @param ParticipantPlanningDisplayer   $participantPlanningDisplayer
     * @param EngineInterface                $templating
     * @param LocalFileStorageAdapter        $localFileStorageAdapter
     * @param MailerInterface                $mailer
     * @param string                         $printPlanningPath
     * @param string                         $mailSender
     * @param FileRepositoryInterface        $fileRepository
     * @param \DateTimeInterface             $dateTime
     * @param TipTranslationViewQueryHandler $tipTranslationViewQueryHandler
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        ParticipantInfoGuesserCache $participantInfoGuesserCache,
        SheetInfoGuesserCache $sheetInfoGuesserCache,
        ParticipantPlanningDisplayer $participantPlanningDisplayer,
        EngineInterface $templating,
        LocalFileStorageAdapter $localFileStorageAdapter,
        MailerInterface $mailer,
        $printPlanningPath,
        $mailSender,
        FileRepositoryInterface $fileRepository,
        \DateTimeInterface $dateTime,
        TipTranslationViewQueryHandler $tipTranslationViewQueryHandler
    ) {
        $this->participantRepository          = $participantRepository;
        $this->participantInfoGuesserCache    = $participantInfoGuesserCache;
        $this->sheetInfoGuesserCache          = $sheetInfoGuesserCache;
        $this->participantPlanningDisplayer   = $participantPlanningDisplayer;
        $this->templating                     = $templating;
        $this->localFileStorageAdapter        = $localFileStorageAdapter;
        $this->mailer                         = $mailer;
        $this->printPlanningPath              = $printPlanningPath;
        $this->mailSender                     = $mailSender;
        $this->fileRepository                 = $fileRepository;
        $this->dateTime                       = $dateTime;
        $this->tipTranslationViewQueryHandler = $tipTranslationViewQueryHandler;
    }

    /**
     * @param ExportPlanning $exportPlanning
     */
    public function handle(ExportPlanning $exportPlanning): void
    {
        /** @var Participant[] $participants */
        $participants = $this->participantRepository->getParticipantsBySheetIdsWithSheetAndTypeHydrated($exportPlanning->sheetIds);

        if (0 === \count($participants)) {
            return;
        }

        /** @var Participant $firstParticipant */
        $firstParticipant = reset($participants);
        $event            = $firstParticipant->getSheet()->getEvent();

        $this->orderParticipant($event, $exportPlanning->orderBy, $participants);

        $plannings = [];
        $this->participantPlanningDisplayer->preloadForUsersAndEvent(
            array_map(function (Participant $participant) {
                return $participant->getUser();
            }, $participants),
            $event
        );

        $tipTranslationViews = [];

        foreach ($participants as $participant) {
            if (!isset($tipTranslationViews[$participant->getLocale()])) {
                $tipTranslationViewQuery = new TipTranslationViewQuery(
                    $participant->getSheet()->getType(),
                    TipTranslationViewQueryHandler::CONTEXT_PRINT_PLANNING,
                    $event->getAvailableLocale($participant->getLocale())
                );
                $tipTranslationViews[$participant->getLocale()] = $this->tipTranslationViewQueryHandler->handle($tipTranslationViewQuery);
            }

            $plannings[] = new PlanningPrint(
                $this->sheetInfoGuesserCache->guessSheetTitle($participant->getSheet(), $event->getFallback()), // Sheet title
                $this->participantInfoGuesserCache->guessParticipantCompleteName($participant, $event->getFallback()), // Participant name
                $this->participantPlanningDisplayer->display($event, $participant->getUser(), $participant->getLocale()), // Planning
                $participant->getLocale(), // participant locale
                $event->getConfiguration()->getLeftColor(), // Left Color
                $event->getConfiguration()->getRightColor(), // Right Color
                $event->getTitle(), // Event Title
                $event->getDescription($event->getAvailableLocale($participant->getLocale())), // Event description
                $event->getDomain(), // Domain
                $participant->getSheet()->getSpot() instanceof Spot ? $participant->getSheet()->getSpot()->getReference() : null,
                $event->getLocalizedLogo($event->getAvailableLocale($participant->getLocale())), // Event logo
                $event->getConfiguration()->getOrganiserWebsite(), // Organiser website
                $event->getConfiguration()->getContactFirstName(), // event contact first name
                $event->getConfiguration()->getContactLastName(), // event contact last name
                $event->getConfiguration()->getOrganiserPhone(), // event organiser phone
                $event->getOrganiserEmail(), // event organiser email
                $tipTranslationViews[$participant->getLocale()] // Tip messages
            );
        }

        $print = $this->templating->render(self::PLANNING_TEMPLATE, [
            'plannings' => $plannings,
        ]);

        $file = $this->createFile($print);

        $this->notifyCreationOfFile($event, $exportPlanning, $file);
    }

    /**
     * @param string $print
     *
     * @return File
     */
    private function createFile(&$print)
    {
        $filePath = $this->localFileStorageAdapter->create($print, 'print_planning.html', $this->printPlanningPath);

        $file = new File($filePath, $this->dateTime);
        $this->fileRepository->add($file);

        return $file;
    }

    /**
     * Send a mail to the emailToNotify with the summary of the orderBy and a link to see the file
     *
     * @param Event          $event
     * @param ExportPlanning $exportPlanning
     * @param File           $file
     */
    private function notifyCreationOfFile(Event $event, ExportPlanning $exportPlanning, File $file): void
    {
        $this->mailer->send(new PrintPlanningMail(
            $event,
            $this->mailSender,
            $exportPlanning->emailToNotify,
            $exportPlanning->locale,
            $file->getHash(),
            $file->getId(),
            $exportPlanning->orderBy
        ));
    }

    /**
     * @param Event         $event
     * @param string        $orderBy
     * @param Participant[] $participants
     */
    private function orderParticipant(Event $event, $orderBy, array &$participants): void
    {
        if (PlanningOrderedBy::ORDER_BY_PARTICIPANT_LAST_NAME === $orderBy) {
            // Load cache for the participant last name to avoid error in the usort
            foreach ($participants as $participant) {
                $this->participantInfoGuesserCache->guessParticipantLastName($participant, $event->getFallback());
            }

            usort($participants, function (Participant $participantLeft, Participant $participantRight) use ($event) {
                $left  = $this->participantInfoGuesserCache->guessParticipantLastName($participantLeft, $event->getFallback());
                $right = $this->participantInfoGuesserCache->guessParticipantLastName($participantRight, $event->getFallback());

                return strcasecmp($left, $right);
            });
        } elseif (PlanningOrderedBy::ORDER_BY_SHEET_TITLE === $orderBy) {
            // Load cache for the sheet title to avoid error in the usort
            foreach ($participants as $participant) {
                $this->sheetInfoGuesserCache->guessSheetTitle($participant->getSheet(), $event->getFallback());
            }

            usort($participants, function (Participant $participantLeft, Participant $participantRight) use ($event) {
                $left  = $this->sheetInfoGuesserCache->guessSheetTitle($participantLeft->getSheet(), $event->getFallback());
                $right = $this->sheetInfoGuesserCache->guessSheetTitle($participantRight->getSheet(), $event->getFallback());

                return strcasecmp($left, $right);
            });
        } elseif (PlanningOrderedBy::ORDER_BY_SPOT_REFERENCE === $orderBy) {
            usort($participants, function (Participant $participantLeft, Participant $participantRight) use ($event) {
                $spotLeftReference = '';
                $spotRightReference = '';
                $spotLeft =  $participantLeft->getSheet()->getSpot();
                $spotRight = $participantRight->getSheet()->getSpot();

                if ($spotLeft instanceof Spot) {
                    $spotLeftReference = $spotLeft->getReference();
                }

                if ($spotRight instanceof Spot) {
                    $spotRightReference = $spotRight->getReference();
                }

                if ($spotLeftReference === $spotRightReference) {
                    $left  = $this->sheetInfoGuesserCache->guessSheetTitle($participantLeft->getSheet(), $event->getFallback());
                    $right = $this->sheetInfoGuesserCache->guessSheetTitle($participantRight->getSheet(), $event->getFallback());

                    return strcasecmp($left, $right);
                }

                return strcasecmp($spotLeftReference, $spotRightReference);
            });
        }
    }
}
