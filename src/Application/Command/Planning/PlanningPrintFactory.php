<?php

namespace Proximum\Vimeet\Application\Command\Planning;

use Proximum\Vimeet\Application\Components\Planning\Displayer\ParticipantPlanningDisplayer;
use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Planning\PlanningPrint;

class PlanningPrintFactory
{
    /** @var SheetInfoGuesserCache */
    private $sheetInfoGuesserCache;

    /** @var ParticipantInfoGuesserCache */
    private $participantInfoGuesserCache;

    /** @var ParticipantPlanningDisplayer */
    private $participantPlanningDisplayer;

    /** @var SheetGuesser */
    private $sheetGuesser;

    /** @var TipTranslationViewQueryHandler */
    private $tipTranslationViewQueryHandler;

    public function __construct(
        SheetInfoGuesserCache $sheetInfoGuesserCache,
        ParticipantInfoGuesserCache $participantInfoGuesserCache,
        ParticipantPlanningDisplayer $participantPlanningDisplayer,
        SheetGuesser $sheetGuesser,
        TipTranslationViewQueryHandler $tipTranslationViewQueryHandler
    ) {
        $this->sheetInfoGuesserCache = $sheetInfoGuesserCache;
        $this->participantInfoGuesserCache = $participantInfoGuesserCache;
        $this->participantPlanningDisplayer = $participantPlanningDisplayer;
        $this->sheetGuesser = $sheetGuesser;
        $this->tipTranslationViewQueryHandler = $tipTranslationViewQueryHandler;
    }

    public function getPlanningPrint(User $user, Event $event, ?Participant $participant): PlanningPrint
    {
        $defaultLocale = $event->getFallback();

        if (!$participant instanceof Participant) {
            $sheet = $this->sheetGuesser->getUserSheet($user, $event, $defaultLocale);
            $participant = $sheet->getUserParticipant($user);

            if (!$participant instanceof Participant) {
                throw new \DomainException('Participant not found');
            }
        }

        return new PlanningPrint(
            $this->sheetInfoGuesserCache->guessSheetTitle($participant->getSheet(), $defaultLocale),
            $this->participantInfoGuesserCache->guessParticipantCompleteName($participant, $defaultLocale),
            $this->participantPlanningDisplayer->display($event, $user, $participant->getLocale()),
            $participant->getLocale(),
            $event->getConfiguration()->getLeftColor(),
            $event->getConfiguration()->getRightColor(),
            $event->getTitle(),
            $event->getDescription($event->getAvailableLocale($participant->getLocale())),
            $event->getDomain(),
            $participant->getSheet()->getSpot() instanceof Spot
                ? $participant->getSheet()->getSpot()->getReference()
                : null,
            $event->getLocalizedLogo($event->getAvailableLocale($participant->getLocale())),
            $event->getConfiguration()->getOrganiserWebsite(),
            $event->getConfiguration()->getContactFirstName(),
            $event->getConfiguration()->getContactLastName(),
            $event->getConfiguration()->getOrganiserPhone(),
            $event->getOrganiserEmail(),
            $this->tipTranslationViewQueryHandler->handle(
                new TipTranslationViewQuery(
                    $participant->getSheet(),
                    $participant->getUser(),
                    TipTranslationViewQueryHandler::CONTEXT_PRINT_PLANNING,
                    $event->getAvailableLocale($participant->getLocale())
                )
            )
        );
    }
}
