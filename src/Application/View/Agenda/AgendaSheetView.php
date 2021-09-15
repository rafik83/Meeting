<?php

namespace Proximum\Vimeet\Application\View\Agenda;

use Proximum\Vimeet\Application\View\Agenda\Admin\RequestView;

class AgendaSheetView
{
    /**
     * @var AgendaParticipantView[]
     */
    public $participants;

    /**
     * @var RequestView[]
     */
    public $requests;

    /**
     * @var AgendaSheetIndicatorView
     */
    public $indicators;

    /**
     * AgendaSheetView constructor.
     *
     * @param AgendaParticipantView[]  $participants
     * @param RequestView[]            $requests
     * @param AgendaSheetIndicatorView $indicators
     */
    public function __construct(
        array $participants,
        array $requests,
        AgendaSheetIndicatorView $indicators
    ) {
        $this->participants = $participants;
        $this->requests     = $requests;
        $this->indicators   = $indicators;
    }
}
