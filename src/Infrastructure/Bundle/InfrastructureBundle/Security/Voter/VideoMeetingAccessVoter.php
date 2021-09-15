<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter;

use Proximum\Vimeet\Application\Components\Security\VideoMeetingAccess;
use Proximum\Vimeet\Domain\Model\Meeting;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class VideoMeetingAccessVoter extends Voter
{
    const PERMISSION = 'PERMISSION_VIDEO_MEETING_ACCESS';

    /**
     * @var VideoMeetingAccess
     */
    private $videoMeetingAccess;

    /**
     * VideoMeetingAccessVoter constructor.
     *
     * @param VideoMeetingAccess $videoMeetingAccess
     */
    public function __construct(VideoMeetingAccess $videoMeetingAccess)
    {
        $this->videoMeetingAccess = $videoMeetingAccess;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof Meeting;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->videoMeetingAccess->allowedToAccess($subject);
    }
}
