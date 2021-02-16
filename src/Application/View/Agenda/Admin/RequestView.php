<?php

namespace Proximum\Vimeet\Application\View\Agenda\Admin;

class RequestView
{
    /** @var int */
    public $requestId;

    /** @var string */
    public $sheetMetTitle;

    /** @var int */
    public $sheetMetId;

    /** @var ParticipantView[] */
    public $participants;

    /** @var bool */
    public $isTransformableIntoMeeting;

    /** @var bool */
    public $isOneOfSheetsNotAttend;

    /** @var bool */
    public $isOneOfSheetsHasNoPreference;

    /**
     * RequestView constructor.
     *
     * @param int               $requestId
     * @param string            $sheetMetTitle
     * @param int               $sheetMetId
     * @param ParticipantView[] $participants
     * @param bool              $isTransformableIntoMeeting
     * @param bool              $isOneOfSheetsNotAttend
     * @param bool              $sheetHasNoPreference
     * @param bool              $sheetMetHasNoPreference
     */
    public function __construct(
        int $requestId,
        string $sheetMetTitle,
        int $sheetMetId,
        array $participants,
        bool $isTransformableIntoMeeting,
        bool $isOneOfSheetsNotAttend,
        bool $sheetHasNoPreference,
        bool $sheetMetHasNoPreference
    ) {
        $this->requestId = $requestId;
        $this->sheetMetTitle = $sheetMetTitle;
        $this->sheetMetId = $sheetMetId;
        $this->participants = $participants;
        $this->isTransformableIntoMeeting = $isTransformableIntoMeeting;
        $this->isOneOfSheetsNotAttend = $isOneOfSheetsNotAttend;
        $this->isOneOfSheetsHasNoPreference = $sheetHasNoPreference || $sheetMetHasNoPreference;
    }
}
