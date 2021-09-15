<?php

namespace Proximum\Vimeet\Domain\KeyDates\Checker;

use Proximum\Vimeet\Domain\Model\Event;

class RegistrationAccessChecker extends AccessChecker
{
    const REGISTRATION_NOT_OPEN = 'registration_not_open';
    const REGISTRATION_CLOSED   = 'registration_closed';
    const REGISTRATION_OPEN     = 'registration_open';

    /** @var RegistrationOpenDateAccessChecker */
    private $registrationDateOpenAccessChecker;

    /** @var RegistrationCloseDateAccessChecker */
    private $registrationDateCloseAccessChecker;

    /**
     * RegistrationAccessChecker constructor.
     *
     * @param \DateTimeInterface                 $dateTime
     * @param RegistrationOpenDateAccessChecker  $registrationDateOpenAccessChecker
     * @param RegistrationCloseDateAccessChecker $registrationDateCloseAccessChecker
     */
    public function __construct(
        \DateTimeInterface $dateTime,
        RegistrationOpenDateAccessChecker $registrationDateOpenAccessChecker,
        RegistrationCloseDateAccessChecker $registrationDateCloseAccessChecker
    ) {
        parent::__construct($dateTime);

        $this->registrationDateOpenAccessChecker = $registrationDateOpenAccessChecker;
        $this->registrationDateCloseAccessChecker = $registrationDateCloseAccessChecker;
    }

    /**
     * @param Event $event
     *
     * @return string
     */
    public function getRegistrationAccessStatus(Event $event): string
    {
        if (!$this->registrationDateOpenAccessChecker->allowedToAccess($event)) {
            return self::REGISTRATION_NOT_OPEN;
        }

        if (!$this->registrationDateCloseAccessChecker->allowedToAccess($event)) {
            return self::REGISTRATION_CLOSED;
        }

        return self::REGISTRATION_OPEN;
    }
}
