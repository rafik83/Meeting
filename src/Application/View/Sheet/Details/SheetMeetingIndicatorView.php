<?php

namespace Proximum\Vimeet\Application\View\Sheet\Details;

class SheetMeetingIndicatorView
{
    /** @var int "nbre de demandes validée" */
    public $approvedRequests;

    /** @var int "nbre de demandes en attente" */
    public $pendingRequests;

    /** @var int "nbre de demandes refusées" */
    public $refusedRequests;

    /** @var int "nbre de propositions validées" */
    public $approvedPropositions;

    /** @var int "nbre de propositions en attente" */
    public $pendingPropositions;

    /** @var int "nbre de proposition refusées" */
    public $refusedPropositions;

    /** @var int number of meetings */
    public $meetings;

    /**
     * SheetMeetingIndicatorView constructor.
     *
     * @param int $approvedRequests
     * @param int $pendingRequests
     * @param int $refusedRequests
     * @param int $approvedPropositions
     * @param int $pendingPropositions
     * @param int $refusedPropositions
     * @param int $meetings
     */
    public function __construct(
        $approvedRequests,
        $pendingRequests,
        $refusedRequests,
        $approvedPropositions,
        $pendingPropositions,
        $refusedPropositions,
        int $meetings
    ) {
        $this->approvedRequests     = $approvedRequests;
        $this->pendingRequests      = $pendingRequests;
        $this->refusedRequests      = $refusedRequests;
        $this->approvedPropositions = $approvedPropositions;
        $this->pendingPropositions  = $pendingPropositions;
        $this->refusedPropositions  = $refusedPropositions;
        $this->meetings             = $meetings;
    }
}
