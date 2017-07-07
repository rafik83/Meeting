<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\KeyDates\Checker;

use Proximum\Vimeet\Domain\Model\Event;

class RegistrationAccessChecker extends AccessChecker
{
    const REGISTRATION_OPEN_DATE_NOT_REACHED = 'registration_open_date_not_reached';
    const REGISTRATION_CLOSE_DATE_REACHED = 'registration_close_date_reached';
    const REGISTRATION_DATE_VALID = 'registration_date_valid';

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
    public function allowedToAccess(Event $event): string
    {
        if (!$this->registrationDateOpenAccessChecker->allowedToAccess($event)) {
            return self::REGISTRATION_OPEN_DATE_NOT_REACHED;
        }

        if (!$this->registrationDateCloseAccessChecker->allowedToAccess($event)) {
            return self::REGISTRATION_CLOSE_DATE_REACHED;
        }

        return self::REGISTRATION_DATE_VALID;
    }
}
