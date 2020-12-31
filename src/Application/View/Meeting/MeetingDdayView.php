<?php

namespace Proximum\Vimeet\Application\View\Meeting;

use IntlDateFormatter;

class MeetingDdayView
{
    /** @var \DateTimeInterface */
    private $datetime;

    /** @var string */
    public $spotName;

    /** @var string */
    public $locale;

    /** @var string[] */
    private $participantsFullname = [];

    /** @var string */
    private $timezone;

    /**
     * MeetingDdayView constructor.
     *
     * @param \DateTimeInterface $datetime
     * @param string             $spotName
     * @param string             $timezone
     * @param string             $locale
     * @param array              $participantsFullname
     */
    public function __construct(
        \DateTimeInterface $datetime,
        string $spotName,
        string $timezone,
        string $locale,
        array $participantsFullname
    ) {
        $this->datetime             = $datetime;
        $this->spotName             = $spotName;
        $this->locale               = $locale;
        $this->timezone             = $timezone;
        $this->participantsFullname = $participantsFullname;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        $dayFormatter = new \IntlDateFormatter(
            $this->locale,
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE,
            $this->timezone
        );

        return $dayFormatter->format($this->datetime);
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        $timeFormatter = new IntlDateFormatter(
            $this->locale,
            IntlDateFormatter::NONE,
            IntlDateFormatter::SHORT,
            $this->timezone
        );

        return $timeFormatter->format($this->datetime);
    }

    /**
     * @return string
     */
    public function getParticipants(): string
    {
        return implode(', ', $this->participantsFullname);
    }
}
