<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use OpenTok\Role;
use Proximum\Vimeet\Application\Adapter\VideoConferenceInterface;
use Proximum\Vimeet\Application\View\Meeting\VideoConferenceView;

class VideoConferenceViewQueryHandler
{
    /**
     * @var VideoConferenceInterface
     */
    private $videoConference;

    /**
     * VideoConferenceViewQueryHandler constructor.
     *
     * @param VideoConferenceInterface $videoConference
     */
    public function __construct(VideoConferenceInterface $videoConference)
    {
        $this->videoConference = $videoConference;
    }

    /**
     * @param VideoConferenceViewQuery $query
     *
     * @return VideoConferenceView
     */
    public function handle(VideoConferenceViewQuery $query)
    {
        $session = $this->videoConference->createSession();

        $slotEndDate    = clone $query->slot->getEnd();
        $sessionEndDate = $slotEndDate->modify('+15 min');

        $token = $this->videoConference->generateAccessToken($session, [
            'role' => Role::PUBLISHER,
//            'expireTime' => $sessionEndDate->getTimeStamp()
        ]);

        return new VideoConferenceView(
            $token,
            $session->getSessionId(),
            $this->videoConference->getApiKey()
        );
    }
}
