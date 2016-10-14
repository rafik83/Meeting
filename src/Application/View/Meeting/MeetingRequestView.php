<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting;

use Proximum\Vimeet\Application\View\Sheet\Preview\PreviewView;
use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingRequestView
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $state;

    /**
     * @var string
     */
    public $type;

    /**
     * @var \DateTimeInterface
     */
    public $createdAt;

    /**
     * @var MeetingRequest
     */
    public $meetingRequest;

    /**
     * @var PreviewView[]
     */
    public $previewViews;

    /**
     * @var string
     */
    public $sheetName;

    /**
     * MeetingRequestView constructor.
     *
     * @param Sheet              $sheet
     * @param string             $sheetName
     * @param string             $state
     * @param string             $type
     * @param \DateTimeInterface $createdAt
     * @param MeetingRequest     $meetingRequest
     * @param PreviewView[]      $previewViews
     */
    public function __construct(
        Sheet $sheet,
        $sheetName,
        $state,
        $type,
        \DateTimeInterface $createdAt,
        MeetingRequest $meetingRequest,
        array $previewViews
    ) {
        $this->sheet          = $sheet;
        $this->sheetName      = $sheetName;
        $this->state          = $state;
        $this->type           = $type;
        $this->createdAt      = $createdAt;
        $this->meetingRequest = $meetingRequest;
        $this->previewViews   = $previewViews;
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return $this->meetingRequest->isSent();
    }

    /**
     * @return bool
     */
    public function isProposition()
    {
        return $this->meetingRequest->getFromSheet() === $this->sheet;
    }
}
