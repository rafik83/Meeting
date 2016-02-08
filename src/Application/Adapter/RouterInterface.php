<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Meeting\MessageSubjectInterface;
use Proximum\Vimeet\Domain\Model\Sheet;

interface RouterInterface
{
    /**
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return string
     */
    public function generateMeetingRequest(Sheet $sheet, Request $request);

    /**
     * @param Meeting $meeting
     * @param Sheet   $sheet
     *
     * @return string
     */
    public function generateMeeting(Sheet $sheet, Meeting $meeting);

    /**
     * @param MessageSubjectInterface $subject
     * @param Sheet                   $sheet
     *
     * @return string
     */
    public function generateSubject(Sheet $sheet, MessageSubjectInterface $subject);
}
