<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Details;

use Proximum\Vimeet\Application\View\Sheet\Details\Invoice\InvoiceView;
use Proximum\Vimeet\Application\View\Sheet\Details\SheetMeetingIndicatorView;
use Proximum\Vimeet\Application\View\Sheet\Details\SheetParticipantsView;
use Proximum\Vimeet\Domain\Model\Sheet\Comment;
use Proximum\Vimeet\Domain\Model\Trace;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\View\OrderVatView;

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
     * @var SheetParticipantsView
     */
    public $participantsView;

    /**
     * @var SheetMeetingIndicatorView
     */
    public $meetingIndicator;

    /**
     * "Forfait"
     *
     * @var string
     */
    public $package;

    /**
     * @var Comment[]
     */
    public $comments;

    /**
     * @var Trace[]
     */
    public $traces;

    /**
     * @var OrderVatView[]
     */
    public $orderVatViews;

    /**
     * @var Transaction[]
     */
    public $transactions;

    /**
     * @var InvoiceView[]
     */
    public $invoiceViews;

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
     * @var TemplateObject[]
     */
    public $companyObjects;

    /**
     * SheetDetailsView constructor.
     *
     * @param string                    $title
     * @param string                    $state
     * @param SheetParticipantsView     $participantsView
     * @param SheetMeetingIndicatorView $meetingIndicator
     * @param Comment[]                 $comments
     * @param Trace[]                   $traces
     * @param OrderVatView[]            $orderVatViews
     * @param Transaction[]             $transactions
     * @param InvoiceView[]             $invoiceViews
     * @param float                     $total
     * @param float                     $remainingToPay
     * @param int                       $completeness
     * @param TemplateObject[]          $companyObjects
     */
    public function __construct(
        $title,
        $state,
        SheetParticipantsView $participantsView,
        SheetMeetingIndicatorView $meetingIndicator,
        array $comments,
        array $traces,
        array $orderVatViews,
        array $transactions,
        array $invoiceViews,
        $total,
        $remainingToPay,
        $completeness,
        array $companyObjects
    ) {
        $this->title            = $title;
        $this->state            = $state;
        $this->comments         = $comments;
        $this->traces           = $traces;
        $this->orderVatViews    = $orderVatViews;
        $this->transactions     = $transactions;
        $this->invoiceViews     = $invoiceViews;
        $this->total            = $total;
        $this->remainingToPay   = $remainingToPay;
        $this->completeness     = $completeness;
        $this->companyObjects   = $companyObjects;
        $this->participantsView = $participantsView;
        $this->meetingIndicator = $meetingIndicator;
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
