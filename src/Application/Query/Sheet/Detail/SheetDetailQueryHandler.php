<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Detail;

use Proximum\Vimeet\Application\Components\Sheet\Details\Invoice\InvoiceViewQuery;
use Proximum\Vimeet\Application\Components\Sheet\Details\Invoice\InvoiceViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Details\SheetDetailsView;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Repository\Sheet\CommentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TraceRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class SheetDetailQueryHandler
{
    /** @var CommentRepositoryInterface */
    private $commentRepository;

    /** @var TraceRepositoryInterface */
    private $traceRepository;

    /** @var Balance */
    private $balance;

    /** @var InvoiceViewQueryHandler */
    private $invoiceViewQueryHandler;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var SheetMeetingIndicatorQueryHandler */
    private $sheetMeetingIndicatorQueryHandler;

    /** @var ParticipantDetailQueryHandler */
    private $participantDetailQueryHandler;

    /**
     * SheetDetailQueryHandler constructor.
     *
     * @param CommentRepositoryInterface        $commentRepository
     * @param TraceRepositoryInterface          $traceRepository
     * @param Balance                           $balance
     * @param InvoiceViewQueryHandler           $invoiceViewQueryHandler
     * @param SheetMeetingIndicatorQueryHandler $sheetMeetingIndicatorQueryHandler
     * @param ParticipantDetailQueryHandler     $participantDetailQueryHandler
     * @param TemplateDataFactory               $templateDataFactory
     */
    public function __construct(
        CommentRepositoryInterface $commentRepository,
        TraceRepositoryInterface $traceRepository,
        Balance $balance,
        InvoiceViewQueryHandler $invoiceViewQueryHandler,
        SheetMeetingIndicatorQueryHandler $sheetMeetingIndicatorQueryHandler,
        ParticipantDetailQueryHandler $participantDetailQueryHandler,
        TemplateDataFactory $templateDataFactory
    ) {
        $this->commentRepository                 = $commentRepository;
        $this->traceRepository                   = $traceRepository;
        $this->balance                           = $balance;
        $this->invoiceViewQueryHandler           = $invoiceViewQueryHandler;
        $this->templateDataFactory               = $templateDataFactory;
        $this->sheetMeetingIndicatorQueryHandler = $sheetMeetingIndicatorQueryHandler;
        $this->participantDetailQueryHandler     = $participantDetailQueryHandler;
    }

    /**
     * @param SheetDetailQuery $query
     *
     * @return SheetDetailsView
     */
    public function handle(SheetDetailQuery $query): SheetDetailsView
    {
        return new SheetDetailsView(
            $query->sheet->getTitle(),
            $query->sheet->getState(),
            $this->participantDetailQueryHandler->handle(
                new ParticipantDetailQuery($query->sheet, $query->locale)
            ),
            $this->sheetMeetingIndicatorQueryHandler->handle(
                new SheetMeetingIndicatorQuery($query->sheet)
            ),
            $this->commentRepository->getCommentsBySheet($query->sheet),
            // Trace for accepted
            $this->traceRepository->getAllTracesByObject($query->sheet),
            $this->balance->getOrderVatViews($query->sheet),
            $this->balance->getTransactions($query->sheet),
            // InvoiceView[]
            $this->invoiceViewQueryHandler->handle(new InvoiceViewQuery($query->sheet)),
            $this->balance->getTotal($query->sheet),
            // Remaining to pay
            $this->balance->getBalance($query->sheet),
            $query->sheet->getCompleteness(),
            // Company Objects
            $this->templateDataFactory
                ->createCompanyTemplate($query->sheet, $query->locale)
                ->getEditableSheetDataExceptedImageObjects()
        );
    }
}
