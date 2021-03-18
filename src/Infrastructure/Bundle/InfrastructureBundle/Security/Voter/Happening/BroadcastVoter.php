<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\Happening;

use Proximum\Vimeet\Domain\Happening\IsUserSpeaker;
use Proximum\Vimeet\Domain\Happening\Webinar\Broadcast\CanWebinarBeBroadcast;
use Proximum\Vimeet\Domain\Model\Happening;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BroadcastVoter extends Voter
{
    public const CAN_STREAM_BROADCAST = 'PERMISSION_HAPPENING_STREAM_BROADCAST';

    /** @var IsUserSpeaker */
    private $isUserSpeaker;

    /** @var CanWebinarBeBroadcast */
    private $canWebinarBeBroadcast;

    public function __construct(IsUserSpeaker $isUserSpeaker, CanWebinarBeBroadcast $canWebinarBeBroadcast)
    {
        $this->isUserSpeaker = $isUserSpeaker;
        $this->canWebinarBeBroadcast = $canWebinarBeBroadcast;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof Happening
            && (self::CAN_STREAM_BROADCAST === $attribute)
        ;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        $happening = $subject;


        if ($attribute === self::CAN_STREAM_BROADCAST) {
            if (!$this->isUserSpeaker->__invoke($happening, $user)) {
                return false;
            }

            if (!$this->canWebinarBeBroadcast->__invoke($happening)) {
                return false;
            }

            return true;
        }
    }
}
