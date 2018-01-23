<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Detail;

use Proximum\Vimeet\Application\Query\Sheet\Detail\Participant\AgendaConfirmationStatusQuery;
use Proximum\Vimeet\Application\Query\Sheet\Detail\Participant\AgendaConfirmationStatusQueryHandler;
use Proximum\Vimeet\Application\Query\Sheet\Detail\Participant\AvailabilityConfirmationStatusQuery;
use Proximum\Vimeet\Application\Query\Sheet\Detail\Participant\AvailabilityConfirmationStatusQueryHandler;
use Proximum\Vimeet\Application\Query\Sheet\Detail\Participant\PhoneValidationStatusQuery;
use Proximum\Vimeet\Application\Query\Sheet\Detail\Participant\PhoneValidationStatusQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Details\OwnerView;
use Proximum\Vimeet\Application\View\Sheet\Details\ParticipantView;
use Proximum\Vimeet\Application\View\Sheet\Details\SheetParticipantsView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class ParticipantDetailQueryHandler
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var AgendaConfirmationStatusQueryHandler */
    private $agendaConfirmationStatusQueryHandler;

    /** @var PhoneValidationStatusQueryHandler */
    private $phoneValidationStatusQueryHandler;

    /** @var AvailabilityConfirmationStatusQueryHandler */
    private $availabilityConfirmationStatusQueryHandler;

    /**
     * ParticipantDetailQueryHandler constructor.
     *
     * @param TemplateDataFactory                        $templateDataFactory
     * @param AgendaConfirmationStatusQueryHandler       $agendaConfirmationStatusQueryHandler
     * @param PhoneValidationStatusQueryHandler          $phoneValidationStatusQueryHandler
     * @param AvailabilityConfirmationStatusQueryHandler $availabilityConfirmationStatusQueryHandler
     */
    public function __construct(
        TemplateDataFactory $templateDataFactory,
        AgendaConfirmationStatusQueryHandler $agendaConfirmationStatusQueryHandler,
        PhoneValidationStatusQueryHandler $phoneValidationStatusQueryHandler,
        AvailabilityConfirmationStatusQueryHandler $availabilityConfirmationStatusQueryHandler
    ) {
        $this->templateDataFactory = $templateDataFactory;
        $this->agendaConfirmationStatusQueryHandler = $agendaConfirmationStatusQueryHandler;
        $this->phoneValidationStatusQueryHandler = $phoneValidationStatusQueryHandler;
        $this->availabilityConfirmationStatusQueryHandler = $availabilityConfirmationStatusQueryHandler;
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
                $this->agendaConfirmationStatusQueryHandler->handle(
                    new AgendaConfirmationStatusQuery($participant, $query->event)
                ),
                $this->phoneValidationStatusQueryHandler->handle(new PhoneValidationStatusQuery($participant)),
                $this->availabilityConfirmationStatusQueryHandler->handle(
                    new AvailabilityConfirmationStatusQuery($query->event, $participant->getUser())
                )
            );
        }

        return new SheetParticipantsView($ownerView, $participantViews);
    }
}
