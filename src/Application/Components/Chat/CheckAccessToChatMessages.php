<?php

namespace Proximum\Vimeet\Application\Components\Chat;

use Proximum\Vimeet\Application\Components\Meeting\CheckAccessToVideoMeeting;
use Proximum\Vimeet\Application\Query\Happening\Webinar\CanAccessToWebinar;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;
use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\User;

class CheckAccessToChatMessages
{
    /** @var CanAccessToWebinar */
    private $canAccessToWebinar;

    /** @var CheckAccessToVideoMeeting */
    private $checkAccessToVideoMeeting;

    /** @var NetworkingAccessChecker */
    private $networkingAccessChecker;

    public function __construct(
        CanAccessToWebinar $canAccessToWebinar,
        CheckAccessToVideoMeeting $checkAccessToVideoMeeting,
        NetworkingAccessChecker $networkingAccessChecker
    ) {
        $this->canAccessToWebinar = $canAccessToWebinar;
        $this->checkAccessToVideoMeeting = $checkAccessToVideoMeeting;
        $this->networkingAccessChecker = $networkingAccessChecker;
    }

    public function isSatisfiedBy(ChatMessageLinkableInterface $object, User $user): bool
    {
        if ($object instanceof Happening) {
            return $this->canAccessToWebinar->isSatisfiableBy($object, $user);
        }

        if ($object instanceof Meeting) {
            return $this->checkAccessToVideoMeeting->isSatisfiedBy($object, $user);
        }

        if ($object instanceof Event) {
            return $this->networkingAccessChecker->allowedToAccess($object);
        }

        return false;
    }
}
