<?php

namespace Proximum\Vimeet\Application\Command\Planning;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Sheet\Batch;
use Proximum\Vimeet\Application\Components\Planning\Displayer\ParticipantPlanningDisplayer;
use Proximum\Vimeet\Application\Query\Badge\GetUserBadgeAndPlanningByEventQuery;
use Proximum\Vimeet\Application\Query\Badge\GetUserBadgeByEventQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Planning\PlanningOrderedBy;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\PrintPlanningMail;
use Twig\Environment;

class ExportPlanningHandler
{
    public const PLANNING_TEMPLATE = 'AdminBundle:Planning/Print:plannings.html.twig';

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var ParticipantInfoGuesserCache */
    private $participantInfoGuesserCache;

    /** @var SheetInfoGuesserCache */
    private $sheetInfoGuesserCache;

    /** @var ParticipantPlanningDisplayer */
    private $participantPlanningDisplayer;

    /** @var Environment */
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

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var PlanningPrintFactory */
    private $planningPrintFactory;

    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        ParticipantInfoGuesserCache $participantInfoGuesserCache,
        SheetInfoGuesserCache $sheetInfoGuesserCache,
        ParticipantPlanningDisplayer $participantPlanningDisplayer,
        Environment $templating,
        LocalFileStorageAdapter $localFileStorageAdapter,
        MailerInterface $mailer,
        $printPlanningPath,
        $mailSender,
        FileRepositoryInterface $fileRepository,
        \DateTimeInterface $dateTime,
        QueryBusInterface $queryBus,
        PlanningPrintFactory $planningPrintFactory
    ) {
        $this->participantRepository = $participantRepository;
        $this->participantInfoGuesserCache = $participantInfoGuesserCache;
        $this->sheetInfoGuesserCache = $sheetInfoGuesserCache;
        $this->participantPlanningDisplayer = $participantPlanningDisplayer;
        $this->templating = $templating;
        $this->localFileStorageAdapter = $localFileStorageAdapter;
        $this->mailer = $mailer;
        $this->printPlanningPath = $printPlanningPath;
        $this->mailSender = $mailSender;
        $this->fileRepository = $fileRepository;
        $this->dateTime = $dateTime;
        $this->queryBus = $queryBus;
        $this->planningPrintFactory = $planningPrintFactory;
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
        $event = $firstParticipant->getSheet()->getEvent();

        $this->orderParticipant($event, $exportPlanning->orderBy, $participants);

        switch ($exportPlanning->printOption) {
            case Batch::PRINT_OPTION_PLANNING:
                $print = $this->handlePlanning($event, $participants);
                break;
            case Batch::PRINT_OPTION_PLANNING_AND_BADGE:
                $print = $this->handlePlanningAndBadge($event, $participants);
                break;
            case Batch::PRINT_OPTION_BADGE:
                $print = $this->handleBadge($event, $participants);
                break;
        }

        $file = $this->createFile($print);

        $this->notifyCreationOfFile($event, $exportPlanning, $file);
    }

    private function handlePlanning(Event $event, array &$participants): string
    {
        $printedUsers = [];
        $plannings = [];
        $this->participantPlanningDisplayer->preloadForUsersAndEvent(
            array_map(function (Participant $participant) {
                return $participant->getUser();
            }, $participants),
            $event
        );

        /** @var Participant $participant */
        foreach ($participants as $participant) {
            $user = $participant->getUser();
            if (array_key_exists($user->getId(), $printedUsers)) {
                continue;
            }

            $plannings[] = $this->planningPrintFactory->getPlanningPrint($user, $event, $participant);
            $printedUsers[$user->getId()] = true;
        }

        return $this->templating->render('AdminBundle:Planning/Print:plannings.html.twig', [
            'plannings' => $plannings,
        ]);
    }

    public function handlePlanningAndBadge(Event $event, array &$participants): string
    {
        $planningsAndBadges = [];
        $printedUsers = [];

        /** @var Participant $participant */
        foreach ($participants as $participant) {
            $user = $participant->getUser();

            if (array_key_exists($user->getId(), $printedUsers)) {
                continue;
            }

            $planningsAndBadges[] = $this->queryBus->handle(
                new GetUserBadgeAndPlanningByEventQuery(
                    $event,
                    $participant->getUser()
                )
            );

            $printedUsers[$user->getId()] = true;
        }

        return $this->templating->render('AdminBundle:Planning/Print:planningsAndBadges.html.twig', [
            'results' => $planningsAndBadges,
        ]);
    }

    public function handleBadge(Event $event, array &$participants): string
    {
        $badges = [];
        $printedUsers = [];

        /** @var Participant $participant */
        foreach ($participants as $participant) {
            $user = $participant->getUser();

            if (array_key_exists($user->getId(), $printedUsers)) {
                continue;
            }

            $badges[] = $this->queryBus->handle(
                new GetUserBadgeByEventQuery(
                    $event,
                    $participant->getUser()
                )
            );

            $printedUsers[$user->getId()] = true;
        }

        return $this->templating->render('AdminBundle:Planning/Print:planningsAndBadges.html.twig', [
            'results' => $badges,
        ]);
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
        $this->mailer->send(
            new PrintPlanningMail(
                $event,
                $this->mailSender,
                $exportPlanning->emailToNotify,
                $exportPlanning->locale,
                $file->getHash(),
                $file->getId(),
                $exportPlanning->orderBy,
                $exportPlanning->printOption
            )
        );
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
                $this->participantInfoGuesserCache->guessParticipantLastName($participant, $event->getLocaleFallback());
            }

            usort($participants, function (Participant $participantLeft, Participant $participantRight) use ($event) {
                $left  = $this->participantInfoGuesserCache->guessParticipantLastName($participantLeft, $event->getLocaleFallback());
                $right = $this->participantInfoGuesserCache->guessParticipantLastName($participantRight, $event->getLocaleFallback());

                return strcasecmp($left, $right);
            });
        } elseif (PlanningOrderedBy::ORDER_BY_SHEET_TITLE === $orderBy) {
            // Load cache for the sheet title to avoid error in the usort
            foreach ($participants as $participant) {
                $this->sheetInfoGuesserCache->guessSheetTitle($participant->getSheet(), $event->getLocaleFallback());
            }

            usort($participants, function (Participant $participantLeft, Participant $participantRight) use ($event) {
                $left  = $this->sheetInfoGuesserCache->guessSheetTitle($participantLeft->getSheet(), $event->getLocaleFallback());
                $right = $this->sheetInfoGuesserCache->guessSheetTitle($participantRight->getSheet(), $event->getLocaleFallback());

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
                    $left  = $this->sheetInfoGuesserCache->guessSheetTitle($participantLeft->getSheet(), $event->getLocaleFallback());
                    $right = $this->sheetInfoGuesserCache->guessSheetTitle($participantRight->getSheet(), $event->getLocaleFallback());

                    return strcasecmp($left, $right);
                }

                return strcasecmp($spotLeftReference, $spotRightReference);
            });
        }
    }
}
