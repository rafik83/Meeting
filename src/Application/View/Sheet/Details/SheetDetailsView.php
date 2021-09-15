<?php

namespace Proximum\Vimeet\Application\View\Sheet\Details;

use Proximum\Vimeet\Application\View\Sheet\Details\CRM\RecordView;
use Proximum\Vimeet\Application\View\Sheet\Details\Invoice\InvoiceView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Trace;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\View\OrderVatView;

class SheetDetailsView
{
    /** @var string */
    public $title;

    /** @var string */
    public $state;

    /** @var SheetParticipantsView */
    public $participants;

    /** @var SheetMeetingIndicatorView */
    public $meetingIndicator;

    /** @var string "Forfait" */
    public $package;

    /** @var RecordView[] */
    public $recordViews;

    /** @var Trace[] */
    public $traces;

    /** @var OrderVatView[] */
    public $orderVatViews;

    /** @var Transaction[] */
    public $transactions;

    /** @var InvoiceView[] */
    public $invoiceViews;

    /** @var float */
    public $total;

    /** @var float */
    public $remainingToPay;

    /** @var int */
    public $completeness;

    /** @var TemplateObject[] */
    public $companyObjects;

    /**
     * @param string                    $title
     * @param string                    $state
     * @param SheetParticipantsView     $participants
     * @param SheetMeetingIndicatorView $meetingIndicator
     * @param RecordView[]              $recordViews
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
        SheetParticipantsView $participants,
        SheetMeetingIndicatorView $meetingIndicator,
        array $recordViews,
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
        $this->recordViews      = $recordViews;
        $this->traces           = $traces;
        $this->orderVatViews    = $orderVatViews;
        $this->transactions     = $transactions;
        $this->invoiceViews     = $invoiceViews;
        $this->total            = $total;
        $this->remainingToPay   = $remainingToPay;
        $this->completeness     = $completeness;
        $this->companyObjects   = $companyObjects;
        $this->participants     = $participants;
        $this->meetingIndicator = $meetingIndicator;
    }

    /**
     * @return string
     */
    public function completenessStatus()
    {
        return Sheet::getCompletenessStatus($this->completeness);
    }
}
