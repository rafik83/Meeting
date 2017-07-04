<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Detail;

use Proximum\Vimeet\Application\View\Sheet\Details\OwnerView;
use Proximum\Vimeet\Application\View\Sheet\Details\ParticipantView;
use Proximum\Vimeet\Application\View\Sheet\Details\SheetParticipantsView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Token\UserEventTokenType;

class ParticipantDetailQueryHandler
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var UserEventTokenRepositoryInterface */
    private $userEventTokenRepository;

    /**
     * ParticipantDetailQueryHandler constructor.
     *
     * @param TemplateDataFactory               $templateDataFactory
     * @param UserEventTokenRepositoryInterface $userEventTokenRepository
     */
    public function __construct(
        TemplateDataFactory $templateDataFactory,
        UserEventTokenRepositoryInterface $userEventTokenRepository
    ) {
        $this->templateDataFactory      = $templateDataFactory;
        $this->userEventTokenRepository = $userEventTokenRepository;
    }

    /**
     * @param ParticipantDetailQuery $query
     *
     * @return SheetParticipantsView
     */
    public function handle(ParticipantDetailQuery $query): SheetParticipantsView
    {
        $sheet            = $query->sheet;
        $participantViews = [];

        $ownerView = new OwnerView(
            $sheet->getOwner(),
            $sheet->getOwner()->getAccount()->getFirstName(),
            $sheet->getOwner()->getAccount()->getLastName(),
            $sheet->getOwner()->getEmail(),
            $sheet->getOwner()->getAccount()->getMobile(),
            $sheet->getOwner()->getAccount()->getPhone(),
            null === $sheet->getParticipantOwner()
        );

        /** @var Participant $participant */
        foreach ($sheet->getParticipants()->toArray() as $participant) {
            $participantViews[] = new ParticipantView(
                $participant->getId(),
                $participant->getEmail(),
                $this->templateDataFactory->createRegistrationFromParticipant(
                    $participant,
                    $query->locale
                ),
                $participant->isOwnerParticipant(),
                $participant->isVisio(),
                $this->userEventTokenRepository->getConfirmationStatusByUserAndType($participant->getUser())
            );
        }

        return new SheetParticipantsView($ownerView, $participantViews);
    }
}
