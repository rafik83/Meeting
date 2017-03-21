<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Details;

use Proximum\Vimeet\Application\Components\Sheet\Details\Invoice\InvoiceViewQuery;
use Proximum\Vimeet\Application\Components\Sheet\Details\Invoice\InvoiceViewQueryHandler;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Sheet\Details\OwnerView;
use Proximum\Vimeet\Application\View\Sheet\Details\ParticipantView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\CommentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TraceRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class SheetDetailsViewFactory
{
    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var CommentRepositoryInterface
     */
    private $commentRepository;

    /**
     * @var TraceRepositoryInterface
     */
    private $traceRepository;

    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var Balance
     */
    private $balance;

    /**
     * @var InvoiceViewQueryHandler
     */
    private $invoiceViewQueryHandler;

    /**
     * SheetDetailsViewFactory constructor.
     *
     * @param SheetInfoGuesser           $sheetInfoGuesser
     * @param RequestRepositoryInterface $requestRepository
     * @param TemplateDataFactory        $templateDataFactory
     * @param CommentRepositoryInterface $commentRepository
     * @param TraceRepositoryInterface   $traceRepository
     * @param Balance                    $balance
     * @param InvoiceViewQueryHandler    $invoiceViewQueryHandler
     */
    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        RequestRepositoryInterface $requestRepository,
        TemplateDataFactory $templateDataFactory,
        CommentRepositoryInterface $commentRepository,
        TraceRepositoryInterface $traceRepository,
        Balance $balance,
        InvoiceViewQueryHandler $invoiceViewQueryHandler
    ) {
        $this->sheetInfoGuesser        = $sheetInfoGuesser;
        $this->requestRepository       = $requestRepository;
        $this->templateDataFactory     = $templateDataFactory;
        $this->commentRepository       = $commentRepository;
        $this->traceRepository         = $traceRepository;
        $this->balance                 = $balance;
        $this->invoiceViewQueryHandler = $invoiceViewQueryHandler;
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return SheetDetailsView
     */
    public function create(Sheet $sheet, $locale)
    {
        $templateDataFactory = $this->templateDataFactory;

        return new SheetDetailsView(
            // Title
            $this->sheetInfoGuesser->guessSheetTitle($sheet, $locale),
            // State
            $sheet->getState(),
            new OwnerView(
                $sheet->getOwner(),
                $sheet->getOwner()->getAccount()->getFirstName(),
                $sheet->getOwner()->getAccount()->getLastName(),
                $sheet->getOwner()->getEmail(),
                $sheet->getOwner()->getAccount()->getMobile(),
                $sheet->getOwner()->getAccount()->getPhone(),
                null === $sheet->getParticipantOwner()
            ),
            // Participant names
            array_map(function (Participant $participant) use ($templateDataFactory, $locale) {
                return new ParticipantView(
                    $participant->getId(),
                    $templateDataFactory->createRegistrationFromParticipant($participant, $locale),
                    $participant->isOwnerParticipant(),
                    $participant->isVisio()
                );
            }, $sheet->getParticipants()->toArray()),
            // Approved requests
            $this->requestRepository->countApprovedRequestSentBySheet($sheet),
            // Pending requests
            $this->requestRepository->countPendingRequestSentBySheet($sheet),
            // Refused requests
            $this->requestRepository->countRefusedRequestSentBySheet($sheet),
            // Approved propositions
            $this->requestRepository->countApprovedPropositionReceivedBySheet($sheet),
            // Pending propositions
            $this->requestRepository->countPendingPropositionReceivedBySheet($sheet),
            // Refused propositions
            $this->requestRepository->countRefusedPropositionReceivedBySheet($sheet),
            // Comments
            $this->commentRepository->getCommentsBySheet($sheet),
            // Trace for accepted
            $this->traceRepository->getAllTracesByObject($sheet),
            // Orders
            $this->balance->getOrders($sheet),
            // Transactions
            $this->balance->getTransactions($sheet),
            // InvoiceView[]
            $this->invoiceViewQueryHandler->handle(new InvoiceViewQuery($sheet)),
            // Total of orders
            $this->balance->getTotal($sheet),
            // Remaining to pay
            $this->balance->getBalance($sheet),
            // Completeness
            $sheet->getCompleteness(),
            // Company Objects
            $templateDataFactory->createCompanyTemplate($sheet, $locale)->getEditableSheetDataExceptedImageObjects()
        );
    }
}
