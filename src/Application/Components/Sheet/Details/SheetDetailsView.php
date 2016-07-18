<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Details;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet\Comment;
use Proximum\Vimeet\Domain\Model\Trace;
use Proximum\Vimeet\Domain\Model\Transaction;

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
     * @var Comment[]
     */
    public $comments;

    /**
     * @var Trace[]
     */
    public $traces;

    /**
     * @var Order[]
     */
    public $orders;

    /**
     * @var Transaction[]
     */
    public $transactions;

    /**
     * @var float
     */
    public $total;

    /**
     * @var float
     */
    public $remainingToPay;

    /**
     * SheetDetailsView constructor.
     *
     * @param string        $title
     * @param string        $state
     * @param array         $participants
     * @param string        $ownerEmail
     * @param string        $ownerPhone
     * @param int           $approvedRequests
     * @param int           $pendingRequests
     * @param int           $refusedRequests
     * @param int           $approvedPropositions
     * @param int           $pendingPropositions
     * @param int           $refusedPropositions
     * @param Comment[]     $comments
     * @param Trace[]       $traces
     * @param Order[]       $orders
     * @param Transaction[] $transactions
     * @param float         $total
     * @param float         $remainingToPay
     */
    public function __construct(
        $title,
        $state,
        array $participants,
        $ownerEmail,
        $ownerPhone,
        $approvedRequests,
        $pendingRequests,
        $refusedRequests,
        $approvedPropositions,
        $pendingPropositions,
        $refusedPropositions,
        array $comments,
        array $traces,
        array $orders,
        array $transactions,
        $total,
        $remainingToPay
    ) {
        $this->title                = $title;
        $this->state                = $state;
        $this->participants         = $participants;
        $this->ownerEmail           = $ownerEmail;
        $this->ownerPhone           = $ownerPhone;
        $this->approvedRequests     = $approvedRequests;
        $this->pendingRequests      = $pendingRequests;
        $this->refusedRequests      = $refusedRequests;
        $this->approvedPropositions = $approvedPropositions;
        $this->pendingPropositions  = $pendingPropositions;
        $this->refusedPropositions  = $refusedPropositions;
        $this->comments             = $comments;
        $this->traces               = $traces;
        $this->orders               = $orders;
        $this->transactions         = $transactions;
        $this->total                = $total;
        $this->remainingToPay       = $remainingToPay;
    }
}
