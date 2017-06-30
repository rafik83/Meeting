<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Details;

class SheetMeetingIndicatorView
{
    /**
     * "nbre de demandes validée"
     *
     * @var int
     */
    public $approvedRequests;

    /**
     * "nbre de demandes en attente"
     *
     * @var int
     */
    public $pendingRequests;

    /**
     * "nbre de demandes refusées"
     *
     * @var int
     */
    public $refusedRequests;

    /**
     * "nbre de propositions validées"
     *
     * @var int
     */
    public $approvedPropositions;

    /**
     * "nbre de propositions en attente"
     *
     * @var int
     */
    public $pendingPropositions;

    /**
     * "nbre de proposition refusées"
     *
     * @var int
     */
    public $refusedPropositions;

    /**
     * SheetMeetingIndicatorView constructor.
     *
     * @param int $approvedRequests
     * @param int $pendingRequests
     * @param int $refusedRequests
     * @param int $approvedPropositions
     * @param int $pendingPropositions
     * @param int $refusedPropositions
     */
    public function __construct(
        $approvedRequests,
        $pendingRequests,
        $refusedRequests,
        $approvedPropositions,
        $pendingPropositions,
        $refusedPropositions
    ) {
        $this->approvedRequests     = $approvedRequests;
        $this->pendingRequests      = $pendingRequests;
        $this->refusedRequests      = $refusedRequests;
        $this->approvedPropositions = $approvedPropositions;
        $this->pendingPropositions  = $pendingPropositions;
        $this->refusedPropositions  = $refusedPropositions;
    }
}
