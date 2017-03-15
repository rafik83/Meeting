<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Application\Components\Sheet\Preview\Preview;
use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Meeting\MeetingRequestView;
use Proximum\Vimeet\Domain\Model\Meeting\Constant;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class MeetingRequestViewQueryHandler
{
    /**
     * @var Preview
     */
    private $preview;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;
    
    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;
    
    /**
     * @var SheetGuesser
     */
    private $sheetGuesser;
    
    /**
     * MeetingRequestViewQueryHandler constructor.
     *
     * @param Preview                   $preview
     * @param SheetInfoGuesser          $sheetInfoGuesser
     * @param RuleRepositoryInterface   $ruleRepository
     * @param SheetGuesser              $sheetGuesser
     */
    public function __construct(
        Preview $preview,
        SheetInfoGuesser $sheetInfoGuesser,
        RuleRepositoryInterface $ruleRepository,
        SheetGuesser $sheetGuesser
    ) {
        $this->preview          = $preview;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->ruleRepository   = $ruleRepository;
        $this->sheetGuesser     = $sheetGuesser;
    }

    /**
     * @param MeetingRequestViewQuery $query
     *
     * @return MeetingRequestView
     */
    public function handle(MeetingRequestViewQuery $query)
    {
        $sheet    = $this->getViewedSheet($query);
        $previews = $this->preview->getPreview($sheet, $query->locale);

        $userSheet = $this->sheetGuesser->getUserSheet($query->user, $sheet->getEvent(), $query->user->getLocale());
    
        $rules = $this->ruleRepository->getBySeerTypeAndSeeableType($userSheet->getType(), $sheet->getType());
        
        $isSheetSeeable = !empty($rules) ? true : false;
        
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
            $isSheetSeeable
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
