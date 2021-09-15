<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Application\Query\Sheet\Planning\SheetPlanningViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Planning\SheetPlanningViewQueryHandler;

class SheetPlanningSubstitution implements SubstituteInterface
{
    /** @var SheetPlanningViewQueryHandler */
    private $sheetPlanningViewQueryHandler;

    public function __construct(SheetPlanningViewQueryHandler $sheetPlanningViewQueryHandler)
    {
        $this->sheetPlanningViewQueryHandler = $sheetPlanningViewQueryHandler;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if (!$prepareMail->hasSheet()) {
            return '';
        }

        if (method_exists($prepareMail, 'getParticipant')) {
            $participant = $prepareMail->getParticipant();
        } else {
            $participant = $prepareMail->sheet->getUserParticipant($prepareMail->user);
        }

        $sheetPlanning = $this->sheetPlanningViewQueryHandler->handle(
            new SheetPlanningViewQuery(
                $prepareMail->sheet,
                $prepareMail->event->getAvailableLocale($prepareMail->locale),
                $participant
            )
        );

        return $sheetPlanning->planning;
    }
}
