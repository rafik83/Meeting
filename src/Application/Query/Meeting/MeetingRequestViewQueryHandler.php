<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Sheet\Preview\Preview;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Meeting\MeetingRequestView;
use Proximum\Vimeet\Domain\Model\Meeting\Constant;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
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

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * MeetingRequestViewQueryHandler constructor.
     *
     * @param Preview                 $preview
     * @param SheetInfoGuesser        $sheetInfoGuesser
     * @param RuleRepositoryInterface $ruleRepository
     * @param Composer                $ruleComposer
     * @param RouterInterface         $router
     */
    public function __construct(
        Preview $preview,
        SheetInfoGuesser $sheetInfoGuesser,
        RuleRepositoryInterface $ruleRepository,
        Composer $ruleComposer,
        RouterInterface $router
    ) {
        $this->preview          = $preview;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->ruleRepository   = $ruleRepository;
        $this->ruleComposer     = $ruleComposer;
        $this->router           = $router;
    }

    /**
     * @param MeetingRequestViewQuery $query
     *
     * @return MeetingRequestView
     */
    public function handle(MeetingRequestViewQuery $query)
    {
        $sheet        = $this->getViewedSheet($query);
        $userSheet    = $query->sheet;
        $rules        = $this->ruleRepository->getBySeerTypeAndSeeableType($userSheet->getType(), $sheet->getType());
        $composedRule = null;

        if (!empty($rules)) {
            $composedRule = $this->ruleComposer->compose($rules);
        }

        $previews = $this->preview->getPreview($sheet, $query->locale, $composedRule);

        $isSheetSeeable = !empty($rules);

        $participant = $query->sheet->getUserParticipant($query->user);

        if ($participant !== null) {
            $validatePhoneLink = $this->router->generate('event_user_phone_redirect_to_validation', [
                'sheet'       => $query->sheet->getId(),
                'participant' => $participant->getId(),
                'redirectTo' => $this->router->generate('event_meeting_list_request', [
                    'sheet' => $query->sheet->getId()
                ])
            ]);
        }

        return new MeetingRequestView(
            $sheet,
            $this->sheetInfoGuesser->guessSheetTitle($sheet, $query->locale),
            $this->getFilterState($query),
            $sheet->getType()->getTitle($query->locale),
            $query->meetingRequest->getCreatedAt(),
            $query->meetingRequest,
            $previews,
            $query->isMeetingPublished,
            $query->isMeetingRequestUpdateLocked,
            $isSheetSeeable,
            $query->isMeetingRequestClosed,
            $query->isAnsweringMeetingRequestClosed,
            $query->meetingRequest->hasMessage(),
            $query->isSeenByUser,
            $query->isPhoneValidationRequired,
            $validatePhoneLink ?? null
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
        if ($query->meetingRequest->getState() === Request::STATE_SENT) {
            if ($query->meetingRequest->getFromSheet() === $query->sheet) {
                return Constant::FILTER_STATE_SENT;
            }

            if ($query->meetingRequest->getToSheet() === $query->sheet) {
                return Constant::FILTER_STATE_RECEIVE;
            }
        }

        return $query->meetingRequest->getState();
    }

    /**
     * Guess what sheet need to be displayed
     *
     * @param MeetingRequestViewQuery $query
     *
     * @return Sheet
     */
    private function getViewedSheet(MeetingRequestViewQuery $query)
    {
        if ($query->meetingRequest->getFromSheet() === $query->sheet) {
            return $query->meetingRequest->getToSheet();
        }

        return $query->meetingRequest->getFromSheet();
    }
}
