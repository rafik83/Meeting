<?php

namespace Proximum\Vimeet\Application\Components\Meeting;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
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
     * @param string  $locale
     *
     * @return RequestView
     */
    public function generate(Request $request, User $user, Sheet $sheet, $locale)
    {
        $sheetNameFrom = $this->sheetInfoGuesser->guessSheetTitle($request->getFromSheet(), $locale);
        $sheetNameTo   = $this->sheetInfoGuesser->guessSheetTitle($request->getToSheet(), $locale);

        $requestView = new RequestView(
            $request->getId(),
            $request->getFromSheet()->getId(),
            $sheetNameFrom,
            $request->getToSheet()->getId(),
            $sheetNameTo,
            $request->getState(),
            $request->getCreatedAt(),
            $request->getStateUpdatedAt(),
            $this->messageRepository->getLastMessageByRequest($request)
        );

        $requestView->canSee     = $this->requestPermissionManager->isAllowedToSee($user, $request, $sheet);
        $requestView->canEdit    = $this->requestPermissionManager->isAllowedToEdit($user, $request, $sheet);
        $requestView->canCancel  = $this->requestPermissionManager->isAllowedToCancel($user, $request, $sheet);
        $requestView->canRefuse  = $this->requestPermissionManager->isAllowedToRefuse($user, $request, $sheet);
        $requestView->canApprove = $this->requestPermissionManager->isAllowedToApprove($user, $request, $sheet);

        foreach ($request->getFromParticipants() as $participant) {
            $participantInfo = $this->participantInfoGuesser->guessParticipantCompleteName(
                $participant,
                $locale
            );

            $requestView->fromParticipants[] = new ParticipantNameView($participantInfo);
        }

        foreach ($request->getToParticipants() as $participant) {
            $participantInfo = $this->participantInfoGuesser->guessParticipantCompleteName(
                $participant,
                $locale
            );

            $requestView->toParticipants[] = new ParticipantNameView($participantInfo);
        }

        return $requestView;
    }
}
