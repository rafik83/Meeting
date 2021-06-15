<?php

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Sheet\Preview\Preview;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Meeting\MeetingRequestView;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;
use Proximum\Vimeet\Domain\Model\Meeting\Constant;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Rule\Composer;

class MeetingRequestViewQueryHandler
{
    /** @var Preview */
    private $preview;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var RuleRepositoryInterface */
    private $ruleRepository;

    /** @var Composer */
    private $ruleComposer;

    /** @var RouterInterface */
    private $router;

    private NetworkingAccessChecker $networkingAccessChecker;

    public function __construct(
        Preview $preview,
        SheetInfoGuesser $sheetInfoGuesser,
        RuleRepositoryInterface $ruleRepository,
        Composer $ruleComposer,
        NetworkingAccessChecker $networkingAccessChecker,
        RouterInterface $router
    ) {
        $this->preview = $preview;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->ruleRepository = $ruleRepository;
        $this->ruleComposer = $ruleComposer;
        $this->networkingAccessChecker = $networkingAccessChecker;
        $this->router = $router;
    }

    public function handle(MeetingRequestViewQuery $query): MeetingRequestView
    {
        $otherSheet = $query->meetingRequest->getSheetMet($query->sheet);
        $userSheet = $query->sheet;
        $rules = $this->ruleRepository->getBySeerSheetAndSeeableSheet($userSheet, $otherSheet);
        $composedRule = null;

        if (!empty($rules)) {
            $composedRule = $this->ruleComposer->compose($rules);
        }

        $showMeetOnline = $this->networkingAccessChecker->isSheetAllowedToAccess($query->sheet);

        $previews = $this->preview->getPreview($otherSheet, $query->locale, $composedRule, $showMeetOnline);

        $participant = $query->sheet->getUserParticipant($query->user);

        if (null !== $participant) {
            $validatePhoneLink = $this->router->generate('event_user_phone_redirect_to_validation', [
                'sheet'       => $query->sheet->getId(),
                'participant' => $participant->getId(),
                'redirectTo' => $this->router->generate('event_catalog_complete_sheet', [
                    'sheet' => $userSheet->getId(),
                    'sheetToDisplay' => $otherSheet->getId(),
                ]),
            ]);
        }

        return new MeetingRequestView(
            $otherSheet,
            $this->sheetInfoGuesser->guessSheetTitle($otherSheet, $query->locale),
            $this->getFilterState($query),
            !$query->showCategory ? $otherSheet->getTypeTitle($query->locale) : $otherSheet->getCategoriesTitles($query->locale),
            $query->meetingRequest->getCreatedAt(),
            $query->meetingRequest,
            $previews,
            $query->isMeetingPublished,
            $query->isMeetingRequestUpdateLocked,
            $query->isMeetingRequestClosed,
            $query->isAnsweringMeetingRequestClosed,
            $query->meetingRequest->hasMessage(),
            $query->isSeenByUser,
            $query->isPhoneValidationRequired,
            $validatePhoneLink ?? null,
            $query->isPriority
        );
    }

    /**
     * Guess meeting request state
     *
     * @param MeetingRequestViewQuery $query
     *
     * @return string
     */
    private function getFilterState(MeetingRequestViewQuery $query)
    {
        if (Request::STATE_SENT === $query->meetingRequest->getState()) {
            if ($query->meetingRequest->getFromSheet() === $query->sheet) {
                return Constant::FILTER_STATE_SENT;
            }

            if ($query->meetingRequest->getToSheet() === $query->sheet) {
                return Constant::FILTER_STATE_RECEIVE;
            }
        }

        return $query->meetingRequest->getState();
    }
}
