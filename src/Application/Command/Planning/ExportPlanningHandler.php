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
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Planning\PlanningOrderedBy;
use Proximum\Vimeet\Domain\Planning\PlanningPrint;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\PrintPlanningMail;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class ExportPlanningHandler
{
    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var ParticipantInfoGuesserCache */
    private $participantInfoGuesserCache;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var SheetInfoGuesserCache */
    private $sheetInfoGuesser;

    /** @var ParticipantPlanningDisplayer */
    private $participantPlanningDisplayer;

    /** @var EngineInterface */
    private $templating;

    /** @var LocalFileStorageAdapter */
    private $localFileStorageAdapter;

    /** @var string */
    private $publicDir;

    /** @var MailerInterface */
    private $mailer;

    /** @var string */
    private $mailSender;

    /**
     * @param TypeRepositoryInterface        $typeRepository
     * @param ParticipantRepositoryInterface $participantRepository
     * @param ParticipantInfoGuesser         $participantInfoGuesser
     * @param SheetInfoGuesser               $sheetInfoGuesser
     * @param ParticipantPlanningDisplayer   $participantPlanningDisplayer
     * @param EngineInterface                $templating
     * @param LocalFileStorageAdapter        $localFileStorageAdapter
     * @param MailerInterface                $mailer
     * @param string                         $publicDir
     * @param string                         $mailSender
     */
    public function __construct(
        TypeRepositoryInterface $typeRepository,
        ParticipantRepositoryInterface $participantRepository,
        ParticipantInfoGuesser $participantInfoGuesser,
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantPlanningDisplayer $participantPlanningDisplayer,
        EngineInterface $templating,
        LocalFileStorageAdapter $localFileStorageAdapter,
        MailerInterface $mailer,
        $publicDir,
        $mailSender
    ) {
        $this->typeRepository               = $typeRepository;
        $this->participantRepository        = $participantRepository;
        $this->participantInfoGuesserCache  = new ParticipantInfoGuesserCache($participantInfoGuesser);
        $this->sheetInfoGuesser             = new SheetInfoGuesserCache($sheetInfoGuesser);
        $this->participantPlanningDisplayer = $participantPlanningDisplayer;
        $this->participantInfoGuesser       = $participantInfoGuesser;
        $this->templating                   = $templating;
        $this->localFileStorageAdapter      = $localFileStorageAdapter;
        $this->mailer                       = $mailer;
        $this->publicDir                    = $publicDir;
        $this->mailSender                   = $mailSender;
    }

    /**
     * @param ExportPlanning $exportPlanning
     */
    public function handle(ExportPlanning $exportPlanning)
    {
        $participants = $this->participantRepository->getParticipantsWithSheetInCatalogAndActiveByTypeIds($exportPlanning->typeIds);

        if (count($participants) === 0) {
            return;
        }

        /** @var Participant $firstParticipant */
        $firstParticipant = reset($participants);
        $event            = $firstParticipant->getSheet()->getEvent();
        $types            = $this->typeRepository->getTypeViewsByIds($exportPlanning->typeIds, $event->getAvailableLocale($exportPlanning->locale));

        if ($exportPlanning->orderBy === PlanningOrderedBy::ORDER_BY_PARTICIPANT_LAST_NAME) {
            // Load cache for the participant last name to avoid error in the usort
            foreach ($participants as $participant) {
                $this->participantInfoGuesser->guessParticipantLastName($participant, $event->getFallback());
            }

            usort($participants, function (Participant $participantLeft, Participant $participantRight) use ($event) {
                $left  = $this->participantInfoGuesserCache->guessParticipantLastName($participantLeft, $event->getFallback());
                $right = $this->participantInfoGuesserCache->guessParticipantLastName($participantRight, $event->getFallback());

                return strcasecmp($left, $right);
            });
        } elseif ($exportPlanning->orderBy === PlanningOrderedBy::ORDER_BY_SHEET_TITLE) {
            // Load cache for the sheet title to avoid error in the usort
            foreach ($participants as $participant) {
                $this->sheetInfoGuesser->guessSheetTitle($participant->getSheet(), $event->getFallback());
            }

            usort($participants, function (Participant $participantLeft, Participant $participantRight) use ($event) {
                $left  = $this->sheetInfoGuesser->guessSheetTitle($participantLeft->getSheet(), $event->getFallback());
                $right = $this->sheetInfoGuesser->guessSheetTitle($participantRight->getSheet(), $event->getFallback());

                return strcasecmp($left, $right);
            });
        }

        $plannings = [];

        foreach ($participants as $participant) {
            $plannings[] = new PlanningPrint(
                $this->sheetInfoGuesser->guessSheetTitle($participant->getSheet(), $event->getFallback()), // Sheet title
                $this->participantInfoGuesser->guessParticipantCompleteName($participant, $event->getFallback()), // Participant name
                $this->participantPlanningDisplayer->display($participant, $participant->getLocale()), // Planning
                $participant->getLocale(), // participant locale
                $event->getConfiguration()->getLeftColor(), // Left Color
                $event->getConfiguration()->getRightColor(), // Right Color
                $event->getTitle(), // Event Title
                $event->getDescription($event->getAvailableLocale($participant->getLocale())), // Event description
                $event->getDomain(), // Domain
                $event->getLogo(), // Event logo
                $event->getConfiguration()->getOrganiserWebsite(), // Organiser website
                $event->getConfiguration()->getContactFirstName(), // event contact first name
                $event->getConfiguration()->getContactLastName(), // event contact last name
                $event->getConfiguration()->getOrganiserPhone(), // event organiser phone
                $event->getOrganiserEmail() // event organiser email
            );
        }

        $print = $this->templating->render('AdminBundle:Planning/Print:plannings.html.twig', [
            'plannings' => $plannings,
        ]);

        $filePath = $this->localFileStorageAdapter->create($print, 'print_planning.html', $this->publicDir);

        $this->mailer->send(new PrintPlanningMail(
            $event,
            $this->mailSender,
            $exportPlanning->emailToNotify,
            $exportPlanning->locale,
            str_replace('/', '-', substr($filePath, 1)),
            $types,
            $exportPlanning->orderBy
        ));
    }
}
