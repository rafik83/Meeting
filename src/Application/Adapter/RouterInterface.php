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

interface RouterInterface
{
    /**
     * @param Request $request
     *
     * @return string
     */
    public function generateMeetingRequest(Request $request);

    /**
     * @param Meeting $meeting
     *
     * @return string
     */
    public function generateMeeting(Meeting $meeting);

    /**
     * @param MessageSubjectInterface $subject
     *
     * @return string
     */
    public function generateSubject(MessageSubjectInterface $subject);
}
