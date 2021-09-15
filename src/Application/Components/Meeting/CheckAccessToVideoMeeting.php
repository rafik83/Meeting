<?php

namespace Proximum\Vimeet\Application\Components\Meeting;

use Proximum\Vimeet\Application\Components\Security\VideoMeetingAccess;
use Proximum\Vimeet\Domain\Exception\Meeting\NoSheetForUserException;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\User;

class CheckAccessToVideoMeeting
{
    /** @var VideoMeetingAccess */
    private $videoMeetingAccess;

    public function __construct(VideoMeetingAccess $videoMeetingAccess)
    {
        $this->videoMeetingAccess = $videoMeetingAccess;
    }

    public function isSatisfiedBy(Meeting $meeting, User $user): bool
    {
        if (!$this->videoMeetingAccess->allowedToAccess($meeting)) {
            return false;
        }

        if (!$meeting->getSpot()->isVisio()) {
            return false;
        }

        try {
            $meeting->getSheetOfUser($user);
        } catch (NoSheetForUserException $noSheetForUserException) {
            return false;
        }

        return true;
    }
}
