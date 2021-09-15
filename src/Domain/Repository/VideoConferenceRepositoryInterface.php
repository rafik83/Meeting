<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\VideoConference;

interface VideoConferenceRepositoryInterface
{
    /**
     * @param Meeting $meeting
     *
     * @return null|VideoConference
     */
    public function findByMeeting(Meeting $meeting): ?VideoConference;

    /**
     * @param VideoConference $videoConference
     */
    public function add(VideoConference $videoConference);

    /**
     * @param VideoConference $videoConference
     */
    public function set(VideoConference $videoConference);
}
