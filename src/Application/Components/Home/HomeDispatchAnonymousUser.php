<?php

namespace Proximum\Vimeet\Application\Components\Home;

use Proximum\Vimeet\Application\View\Home\HomeDispatchAnonymousView;
use Proximum\Vimeet\Domain\KeyDates\Checker\RegistrationAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;

class HomeDispatchAnonymousUser
{
    const TYPE_REGISTRATION_NOT_OPEN = 'registration_not_open';
    const TYPE_REGISTRATION_CLOSED   = 'registration_closed';

    /** @var RegistrationAccessChecker */
    private $registrationAccessChecker;

    /**
     * HomeDispatchAnonymousUser constructor.
     *
     * @param RegistrationAccessChecker $registrationAccessChecker
     */
    public function __construct(RegistrationAccessChecker $registrationAccessChecker)
    {
        $this->registrationAccessChecker = $registrationAccessChecker;
    }

    /**
     * @param Event $event
     *
     * @return null|HomeDispatchAnonymousView
     */
    public function handle(Event $event): ?HomeDispatchAnonymousView
    {
        $registrationAccessStatus = $this->registrationAccessChecker->getRegistrationAccessStatus($event);

        if (in_array($registrationAccessStatus, [self::TYPE_REGISTRATION_NOT_OPEN, self::TYPE_REGISTRATION_CLOSED])) {
            return new HomeDispatchAnonymousView($registrationAccessStatus);
        }

        return null;
    }
}
