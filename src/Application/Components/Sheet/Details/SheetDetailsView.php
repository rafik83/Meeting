<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Details;

use Proximum\Vimeet\Application\Components\Sheet\Block\BlockDataView;
use Proximum\Vimeet\Application\Components\Sheet\Proforma\BillingView;
use Proximum\Vimeet\Domain\Model\Sheet\Comment;

class SheetDetailsView
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $state;

    /**
     * @var array
     */
    public $participants;

    /**
     * @var string
     */
    public $ownerEmail;

    /**
     * @var string
     */
    public $ownerPhone;

    /**
     * "Forfait"
     *
     * @var string
     */
    public $package;

    /**
     * @var BillingView
     */
    public $billing;

    /**
     * @var BlockDataView[]
     */
    public $blocks;

    /**
     * "nbre de demandes validée"
     *
     * @var int
     */
    public $approvedRequests;

    /**
     * "nbre de propositions en attente"
     *
     * @var int
     */
    public $pendingPropositions;

    /**
     * "nbre de demandes refusées"
     *
     * @var int
     */
    public $refusedRequests;

    /**
     * "nbre de proposition refusées"
     *
     * @var int
     */
    public $refusePropositions;

    /**
     * @var Comment[]
     */
    public $comments;

    /**
     * SheetDetailsView constructor.
     *
     * @param string          $title
     * @param string          $state
     * @param array           $participants
     * @param string          $ownerEmail
     * @param string          $ownerPhone
     * @param string          $package
     * @param BillingView     $billing
     * @param BlockDataView[] $blocks
     * @param int             $approvedRequests
     * @param int             $pendingPropositions
     * @param int             $refusedRequests
     * @param int             $refusePropositions
     * @param Comment[]      $comments
     */
    public function __construct($title, $state, array $participants, $ownerEmail, $ownerPhone, $package, BillingView $billing, array $blocks, $approvedRequests, $pendingPropositions, $refusedRequests, $refusePropositions, array $comments)
    {
        $this->title               = $title;
        $this->state               = $state;
        $this->participants        = $participants;
        $this->ownerEmail          = $ownerEmail;
        $this->ownerPhone          = $ownerPhone;
        $this->package             = $package;
        $this->billing             = $billing;
        $this->blocks              = $blocks;
        $this->approvedRequests    = $approvedRequests;
        $this->pendingPropositions = $pendingPropositions;
        $this->refusedRequests     = $refusedRequests;
        $this->refusePropositions  = $refusePropositions;
        $this->comments            = $comments;
    }
}
