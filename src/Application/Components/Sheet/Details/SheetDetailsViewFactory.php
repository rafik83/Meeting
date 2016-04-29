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
use Proximum\Vimeet\Domain\Repository\TraceRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

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
     * @var TraceRepositoryInterface
     */
    private $traceRepository;

    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * SheetDetailsViewFactory constructor.
     *
     * @param SheetInfoGuesser           $sheetInfoGuesser
     * @param ParticipantInfoGuesser     $participantInfoGuesser
     * @param RequestRepositoryInterface $requestRepository
     * @param BillingViewFactory         $billingViewFactory
     * @param BlockDataViewFactory       $blockDataViewFactory
     * @param TemplateDataFactory        $templateDataFactory
     * @param CommentRepositoryInterface $commentRepository
     * @param TraceRepositoryInterface   $traceRepository
     */
    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser,
        RequestRepositoryInterface $requestRepository,
        BillingViewFactory $billingViewFactory,
        BlockDataViewFactory $blockDataViewFactory,
        TemplateDataFactory $templateDataFactory,
        CommentRepositoryInterface $commentRepository,
        TraceRepositoryInterface $traceRepository
    ) {
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->requestRepository      = $requestRepository;
        $this->billingViewFactory     = $billingViewFactory;
        $this->blockDataViewFactory   = $blockDataViewFactory;
        $this->templateDataFactory    = $templateDataFactory;
        $this->commentRepository      = $commentRepository;
        $this->traceRepository        = $traceRepository;
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
            $this->sheetInfoGuesser->guessSheetInfo($sheet),
            // State
            $sheet->getState(),
            // Participant names
            array_map(function (Participant $participant) use ($templateDataFactory, $locale) {
                $objects = $templateDataFactory->createRegistrationFromParticipant($participant, $locale)->getObjects();

                foreach ($objects as $object) {
                    if (null !== $object->getData()) {
                        $infos[$object->getLabel($locale, $participant->getSheet()->getEvent()->getFallback())] = (string) $object;
                    }
                }

                return $infos;
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
            $this->traceRepository->getAllTracesByObject($sheet)
        );
    }
}
