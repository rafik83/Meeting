<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Details;

use Proximum\Vimeet\Application\Components\Sheet\Proforma\BillingViewFactory;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\CommentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TraceRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Object;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
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
     * @param TemplateDataFactory        $templateDataFactory
     * @param CommentRepositoryInterface $commentRepository
     * @param TraceRepositoryInterface   $traceRepository
     */
    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser,
        RequestRepositoryInterface $requestRepository,
        TemplateDataFactory $templateDataFactory,
        CommentRepositoryInterface $commentRepository,
        TraceRepositoryInterface $traceRepository
    ) {
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->requestRepository      = $requestRepository;
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
            $this->sheetInfoGuesser->guessSheetName($sheet, $locale),
            // State
            $sheet->getState(),
            // Participant names
            array_map(function (Participant $participant) use ($templateDataFactory, $locale) {
                $objects = $templateDataFactory->createRegistrationFromParticipant($participant, $locale)->getProfileObjects();

                $infos = [];

                /** @var Object $object */
                foreach ($objects as $object) {
                    if ($object instanceof Object\ContentObjectInterface) {
                        $label = $object->getLabel($locale, $participant->getSheet()->getEvent()->getFallback());
                        if ($object instanceof Object\Nomenclature) {
                            $infos[$label] = $object->getContentLabel();
                        } else {
                            $infos[$label] = $object->getContentValue();
                        }
                    }
                }

                return $infos;
            }, $sheet->getParticipants()->toArray()),
            // Owner email
            $sheet->getOwner()->getEmail(),
            // Owner phone
            null !== $sheet->getParticipantOwner() ? $this->participantInfoGuesser->guessParticipantPhone($sheet->getParticipantOwner(), $locale) : $sheet->getOwner()->getAccount()->getPhone(),
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
