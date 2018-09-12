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
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierQuery;
use Proximum\Vimeet\Application\View\Event\QRCodeIdentifierListView;
use Proximum\Vimeet\Application\View\Event\QRCodeIdentifierView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class GetQRCodeIdentifiersByEventQueryHandler
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var GroupNameResolver */
    private $groupNameResolver;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        QueryBusInterface $queryBus,
        ParticipantRepositoryInterface $participantRepository,
        ParticipantInfoGuesser $participantInfoGuesser,
        GroupNameResolver $groupNameResolver,
        RouterInterface $router
    ) {
        $this->queryBus = $queryBus;
        $this->participantRepository = $participantRepository;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->groupNameResolver = $groupNameResolver;
        $this->router = $router;
    }

    public function handle(GetQRCodeIdentifiersByEventQuery $query): QRCodeIdentifierListView
    {
        $participants = $this->participantRepository->findByEvent($query->event);
        $userSheets = [];
        $qrCodePayloadListView = [];

        foreach ($participants as $participant) {
            $user = $participant->getUser();
            $userId = $user->getId();

            if (\array_key_exists($userId, $qrCodePayloadListView)) {
                $qrCodePayloadListView[$userId]->setSheetTitle(
                    $this->getSheetTitle(
                        $query->event,
                        $user,
                        array_merge([$participant->getSheet()], $userSheets[$userId])
                    )
                );

                continue;
            }

            $participantInfo = $this->participantInfoGuesser->guessParticipantInfos($participant, $query->locale);

            $qrCodePayloadListView[$userId] = new QRCodeIdentifierView(
                $this->getQrCodeIdentifier($query->event, $user),
                $participantInfo['participant_firstname'] ?? '',
                $participantInfo['participant_lastname'] ?? '',
                $this->getSheetTitle($query->event, $user, [$participant->getSheet()]),
                $this->router->generate('admin_user_event_badge',
                    [
                        'user' => $userId,
                        'event' => $query->event->getId(),
                    ]
                )
            );

            $userSheets[$userId][] = $participant->getSheet();
        }

        return new QRCodeIdentifierListView($qrCodePayloadListView);
    }

    private function getSheetTitle(Event $event, User $user, array $sheets): ?string
    {
        try {
            return $this->groupNameResolver->resolve($event, $user, $sheets);
        } catch (SheetNotFoundException $exception) {
            return null;
        }
    }

    private function getQrCodeIdentifier(Event $event, User $user): string
    {
        return $this->queryBus->handle(new QRCodeIdentifierQuery($event, $user));
    }
}
