<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Meeting;

use Proximum\Vimeet\Application\Components\Participant\ParticipantInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\View\Meeting\RequestView;
use Proximum\Vimeet\Domain\View\ParticipantNameView;

class RequestViewBuilder
{
    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @var RequestPermissionManager
     */
    private $requestPermissionManager;

    /**
     * RequestViewBuilder constructor.
     *
     * @param MessageRepositoryInterface $messageRepository
     * @param SheetInfoGuesser           $sheetInfoGuesser
     * @param ParticipantInfoGuesser     $participantInfoGuesser
     * @param RequestPermissionManager   $requestPermissionManager
     */
    public function __construct(
        MessageRepositoryInterface $messageRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser,
        RequestPermissionManager $requestPermissionManager
    ) {
        $this->messageRepository        = $messageRepository;
        $this->sheetInfoGuesser         = $sheetInfoGuesser;
        $this->participantInfoGuesser   = $participantInfoGuesser;
        $this->requestPermissionManager = $requestPermissionManager;
    }

    /**
     * @param Request $request
     * @param User    $user
     * @param Sheet   $sheet
     *
     * @return RequestView
     */
    public function generate(Request $request, User $user, Sheet $sheet)
    {
        $sheetNameFrom = $this->sheetInfoGuesser->guessSheetInfo($request->getFromSheet());
        $sheetNameTo   = $this->sheetInfoGuesser->guessSheetInfo($request->getToSheet());

        $requestView = new RequestView(
            $request->getId(),
            $sheetNameFrom,
            $sheetNameTo,
            $request->getState(),
            $request->getCreatedAt(),
            $this->messageRepository->getLastMessageByRequest($request)
        );

        $requestView->canSee     = $this->requestPermissionManager->isAllowedToSee($user, $request, $sheet);
        $requestView->canEdit    = $this->requestPermissionManager->isAllowedToEdit($user, $request, $sheet);
        $requestView->canCancel  = $this->requestPermissionManager->isAllowedToCancel($user, $request, $sheet);
        $requestView->canRefuse  = $this->requestPermissionManager->isAllowedToRefuse($user, $request, $sheet);
        $requestView->canApprove = $this->requestPermissionManager->isAllowedToApprove($user, $request, $sheet);

        foreach ($request->getFromParticipants() as $participant) {
            $participantInfo                 = $this->participantInfoGuesser->guessParticipantInfo($participant);
            $requestView->fromParticipants[] = new ParticipantNameView($participantInfo);
        }

        foreach ($request->getToParticipants() as $participant) {
            $participantInfo               = $this->participantInfoGuesser->guessParticipantInfo($participant);
            $requestView->toParticipants[] = new ParticipantNameView($participantInfo);
        }

        return $requestView;
    }
}
