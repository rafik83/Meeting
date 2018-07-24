<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Event;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierQuery;
use Proximum\Vimeet\Application\View\Event\QRCodePayloadListView;
use Proximum\Vimeet\Application\View\Event\QRCodePayloadView;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class GetQRCodePayloadByEventQueryHandler
{
    /** @var QueryBusInterface */
    public $queryBus;

    /** @var ParticipantRepositoryInterface */
    public $participantRepository;

    public function __construct(
        QueryBusInterface $queryBus,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->queryBus = $queryBus;
        $this->participantRepository = $participantRepository;
    }

    public function handle(GetQRCodePayloadByEventQuery $query): QRCodePayloadListView
    {
        $participants = $this->participantRepository->findByEvent($query->event);
        $identifiedUsers = [];
        $qrCodePayloadListView = [];

        foreach ($participants as $participant) {
            $user = $participant->getUser();

            if (\in_array($user->getId(), $identifiedUsers, true)) {
                continue;
            }

            $qRCodeIdentifier = $this->queryBus->handle(new QRCodeIdentifierQuery($query->event, $user));
            $qrCodePayloadListView[] = new QRCodePayloadView($qRCodeIdentifier);
            $identifiedUsers[] = $user->getId();
        }

        return new QRCodePayloadListView($qrCodePayloadListView);
    }
}
