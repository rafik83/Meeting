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
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Impersonate\Impersonate;

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

    /** @var IsParticipantVisio */
    private $isParticipantVisio;

    /** @var Impersonate */
    private $impersonate;

    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    public function __construct(
        TemplateDataFactory $templateDataFactory,
        AgendaConfirmationStatusQueryHandler $agendaConfirmationStatusQueryHandler,
        PhoneValidationStatusQueryHandler $phoneValidationStatusQueryHandler,
        AvailabilityConfirmationStatusQueryHandler $availabilityConfirmationStatusQueryHandler,
        IsParticipantVisio $isParticipantVisio,
        Impersonate $impersonate,
        EventUrlGeneratorInterface $eventUrlGenerator
    ) {
        $this->templateDataFactory = $templateDataFactory;
        $this->agendaConfirmationStatusQueryHandler = $agendaConfirmationStatusQueryHandler;
        $this->phoneValidationStatusQueryHandler = $phoneValidationStatusQueryHandler;
        $this->availabilityConfirmationStatusQueryHandler = $availabilityConfirmationStatusQueryHandler;
        $this->isParticipantVisio = $isParticipantVisio;
        $this->impersonate = $impersonate;
        $this->eventUrlGenerator = $eventUrlGenerator;
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

        $owner = $sheet->getOwner();
        $ownerView = new OwnerView(
            $owner,
            $owner->getFirstName(),
            $owner->getLastName(),
            $owner->getEmail(),
            $owner->getMobile(),
            $owner->getPhone(),
            null === $sheet->getParticipantOwner()
        );

        /** @var Participant $participant */
        foreach ($sheet->getParticipants()->toArray() as $participant) {
            $user = $participant->getUser();

            $participantViews[] = new ParticipantView(
                $participant->getId(),
                $participant->getEmail(),
                $this->templateDataFactory->createRegistrationFromParticipant(
                    $participant,
                    $query->locale
                ),
                $participant->isOwnerParticipant(),
                $this->isParticipantVisio->isSatisfiedBy($participant),
                $this->agendaConfirmationStatusQueryHandler->handle(
                    new AgendaConfirmationStatusQuery($participant, $query->event)
                ),
                $this->phoneValidationStatusQueryHandler->handle(new PhoneValidationStatusQuery($participant)),
                $this->availabilityConfirmationStatusQueryHandler->handle(
                    new AvailabilityConfirmationStatusQuery($query->event, $user)
                ),
                $user->getId()
            );
        }

        return new SheetParticipantsView($ownerView, $participantViews);
    }
}
