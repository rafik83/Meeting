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
use Proximum\Vimeet\Application\Components\Sheet\BillingInfoGuesser;
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
     * @var BillingInfoGuesser
     */
    private $billingInfoGuesser;

    /**
     * ProformaViewFactory constructor.
     *
     * @param OrderViewFactory       $orderViewFactory
     * @param ParticipantInfoGuesser $participantInfoGuesser
     * @param BillingInfoGuesser     $billingInfoGuesser
     */
    public function __construct(
        OrderViewFactory $orderViewFactory,
        ParticipantInfoGuesser $participantInfoGuesser,
        BillingInfoGuesser $billingInfoGuesser
    ) {
        $this->orderViewFactory       = $orderViewFactory;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->billingInfoGuesser     = $billingInfoGuesser;
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
        $billingView = new BillingView(
            $this->billingInfoGuesser->getName($sheet),
            $this->billingInfoGuesser->getAddress($sheet),
            $this->billingInfoGuesser->getCity($sheet),
            $this->billingInfoGuesser->getZipcode($sheet),
            $this->billingInfoGuesser->getCountry($sheet),
            $this->billingInfoGuesser->getPhone($sheet),
            $this->billingInfoGuesser->getEmail($sheet),
            $this->billingInfoGuesser->getOrganization($sheet),
            $this->billingInfoGuesser->getVatNumber($sheet),
            $this->billingInfoGuesser->getExtra($sheet)
        );

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
