<?php

namespace Proximum\Vimeet\Application\View\Agenda;

class AgendaParticipantView
{
    /** @var AgendaDayView[] */
    public $days;

    /** @var int */
    public $id;

    /** @var string */
    public $fullname;

    /** @var string */
    public $email;

    /** @var bool */
    public $isAttending;

    /** @var bool */
    public $isSmsActivateDatePassed;

    /** @var bool */
    public $stopSMS;

    /** @var bool */
    public $isPhoneValidated;

    /**
     * @param int             $id
     * @param string          $fullname
     * @param string          $email
     * @param AgendaDayView[] $days
     * @param bool            $isAttending
     * @param bool            $isSmsActivateDatePassed
     * @param bool            $isPhoneValidated
     * @param bool            $stopSMS
     */
    public function __construct(
        int $id,
        string $fullname = null,
        string $email,
        array $days,
        bool $isAttending = true,
        bool $isSmsActivateDatePassed = false,
        bool $isPhoneValidated = false,
        $stopSMS = false
    ) {
        $this->days     = $days;
        $this->id       = $id;
        $this->fullname = $fullname;
        $this->email    = $email;
        $this->isAttending = $isAttending;
        $this->isSmsActivateDatePassed = $isSmsActivateDatePassed;
        $this->isPhoneValidated = $isPhoneValidated;
        $this->stopSMS = $stopSMS;
    }
}
