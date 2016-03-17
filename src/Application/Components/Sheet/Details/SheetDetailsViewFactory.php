<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Details;

use Proximum\Vimeet\Application\Components\Participant\ParticipantInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Block\BlockDataViewFactory;
use Proximum\Vimeet\Application\Components\Sheet\Proforma\BillingViewFactory;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\CommentRepositoryInterface;

class SheetDetailsViewFactory
{
    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var BillingViewFactory
     */
    private $billingViewFactory;

    /**
     * @var BlockDataViewFactory
     */
    private $blockDataViewFactory;

    /**
     * @var CommentRepositoryInterface
     */
    private $commentRepository;

    /**
     * SheetDetailsViewFactory constructor.
     *
     * @param SheetInfoGuesser           $sheetInfoGuesser
     * @param ParticipantInfoGuesser     $participantInfoGuesser
     * @param RequestRepositoryInterface $requestRepository
     * @param BillingViewFactory         $billingViewFactory
     * @param BlockDataViewFactory       $blockDataViewFactory
     * @param CommentRepositoryInterface $commentRepository
     */
    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser,
        RequestRepositoryInterface $requestRepository,
        BillingViewFactory $billingViewFactory,
        BlockDataViewFactory $blockDataViewFactory,
        CommentRepositoryInterface $commentRepository
    ) {
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->requestRepository      = $requestRepository;
        $this->billingViewFactory     = $billingViewFactory;
        $this->blockDataViewFactory   = $blockDataViewFactory;
        $this->commentRepository      = $commentRepository;
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return SheetDetailsView
     */
    public function create(Sheet $sheet, $locale)
    {
        return new SheetDetailsView(
            // Title
            $this->sheetInfoGuesser->guessSheetInfo($sheet),
            // State
            $sheet->getState(),
            // Participant names
            array_map(function (Participant $participant) {
                return $this->participantInfoGuesser->guessParticipantInfo($participant);
            }, $sheet->getParticipants()->toArray()),
            // Owner email
            $sheet->getOwner()->getUser()->getEmail(),
            // Owner phone
            $this->participantInfoGuesser->guessParticipantPhone($sheet->getOwner()),
            // Package
            $this->sheetInfoGuesser->guessSheetPackage($sheet, $locale),
            // Billing
            $this->billingViewFactory->createFromSheet($sheet),
            // Blocks
            $this->blockDataViewFactory->createBlockViews($sheet, $locale),
            // Approved requests
            $this->requestRepository->countApprovedRequestSentBySheet($sheet),
            // Pending propositions
            $this->requestRepository->countPendingPropositionReceivedBySheet($sheet),
            // Refused requests
            $this->requestRepository->countRefusedRequestSentBySheet($sheet),
            // Refused propositions
            $this->requestRepository->countRefusedPropositionReceivedBySheet($sheet),
            // Comments
            $this->commentRepository->getCommentsBySheet($sheet)
        );
    }
}
