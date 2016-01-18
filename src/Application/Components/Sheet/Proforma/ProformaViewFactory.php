<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Proforma;

use Proximum\Vimeet\Application\Components\Sheet\Order\OrderViewFactory;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Application\Components\Participant\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Model\Participant;

class ProformaViewFactory
{
    /**
     * @var OrderViewFactory
     */
    private $orderViewFactory;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * ProformaViewFactory constructor.
     *
     * @param OrderViewFactory       $orderViewFactory
     * @param ParticipantInfoGuesser $participantInfoGuesser
     */
    public function __construct(OrderViewFactory $orderViewFactory, ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->orderViewFactory       = $orderViewFactory;
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param Order  $order
     * @param string $locale
     *
     * @return ProformaView
     */
    public function createFromOrder(Order $order, $locale)
    {
        $sheet = $order->getSheet();
        $event = $sheet->getEvent();

        // Order view
        $orderView = $this->orderViewFactory->createFromOrder($order, $locale);

        // Organizer data
        $organizerView = new OrganizerView(
            $event->getOrganiserName(),
            $event->getPaymentAddress(),
            $event->getOrganiserEmail(),
            $event->getBankInfo(),
            $event->getLegalInformation(),
            $event->getElementToJoinWithInvoice()
        );

        // Billing data
        $billingView = new BillingView();

        // Participant
        $participants = array_map(
            function (Participant $participant) {
                return $this->participantInfoGuesser->guessParticipantInfo($participant);
            }, $sheet->getParticipants()->toArray()
        );

        return new ProformaView(
            $sheet->getEvent()->getTitle(),
            $participants,
            $orderView,
            $organizerView,
            $billingView
        );
    }
}
