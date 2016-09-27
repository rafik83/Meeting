<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Details;

use Proximum\Vimeet\Application\View\Sheet\Details\OwnerView;
use Proximum\Vimeet\Application\View\Sheet\Details\ParticipantView;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet\Comment;
use Proximum\Vimeet\Domain\Model\Trace;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Template\TemplateData;

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
     * @var OwnerView
     */
    public $owner;

    /**
     * @var ParticipantView[]
     */
    public $participants;

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
     * @var int
     */
    public $completeness;

    /**
     * @var TemplateData
     */
    public $companyObjects;

    /**
     * SheetDetailsView constructor.
     *
     * @param string            $title
     * @param string            $state
     * @param OwnerView         $owner
     * @param ParticipantView[] $participants
     * @param int               $approvedRequests
     * @param int               $pendingRequests
     * @param int               $refusedRequests
     * @param int               $approvedPropositions
     * @param int               $pendingPropositions
     * @param int               $refusedPropositions
     * @param Comment[]         $comments
     * @param Trace[]           $traces
     * @param Order[]           $orders
     * @param Transaction[]     $transactions
     * @param float             $total
     * @param float             $remainingToPay
     * @param int               $completeness
     * @param TemplateData      $companyObjects
     */
    public function __construct(
        $title,
        $state,
        OwnerView $owner,
        array $participants,
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
        $remainingToPay,
        $completeness,
        TemplateData $companyObjects
    ) {
        $this->title                = $title;
        $this->state                = $state;
        $this->owner                = $owner;
        $this->participants         = $participants;
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
        $this->completeness         = $completeness;
        $this->companyObjects       = $companyObjects;
    }

    /**
     * @return string
     */
    public function completenessStatus()
    {
        if ($this->completeness < 40) {
            return 'danger';
        }

        if ($this->completeness < 100) {
            return 'warning';
        }

        if ($this->completeness === 100) {
            return 'success';
        }

        return 'danger';
    }
}
