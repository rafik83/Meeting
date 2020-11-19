<?php

namespace Proximum\Vimeet\Application\View\Participant\Export;

class ParticipantView
{
    /** @var int */
    public $sheetId;

    /** @var string */
    public $typeTitle;

    /** @var null|string */
    public $sheetTitle;

    /** @var bool */
    public $sheetEnabled;

    /** @var int */
    public $userId;

    /** @var int */
    public $participantId;

    /** @var string */
    public $email;

    /** @var string DD/MM/YYYY format */
    public $createdAt;

    /** @var bool */
    public $hasHappeningParticipation;

    /** @var bool */
    public $hasPaidParticipation;

    /** @var array of key => exportableContent */
    public $registrationData;

    /** @var null|int */
    public $participantProductId;

    /** @var int[] of product id => product id */
    public $attributableProducts;

    /** @var string[] */
    public $daysChecking;

    /** @var string[] */
    public $happeningChecking;

    /** @var int */
    public $viewedSheets;

    /** @var int */
    public $clickedElements;

    /** @var int */
    public $requestedMeetings;

    /** @var int */
    public $scheduledMeetings;

    /** @var int */
    public $chatSessionsCallVisio;

    public function __construct(
        int $sheetId,
        string $typeTitle,
        ?string $sheetTitle,
        bool $sheetEnabled,
        int $userId,
        int $participantId,
        string $email,
        string $createdAt,
        bool $hasHappeningParticipation,
        bool $hasPaidParticipation,
        ?int $participantProductId,
        array $daysChecking,
        array $attributableProducts,
        array $registrationData,
        array $happeningChecking,
        int $viewedSheets,
        int $clickedElements,
        int $requestedMeetings,
        int $scheduledMeetings,
        int $chatSessionsCallVisio
    ) {
        $this->sheetId = $sheetId;
        $this->typeTitle = $typeTitle;
        $this->sheetTitle = $sheetTitle;
        $this->sheetEnabled = $sheetEnabled;
        $this->userId = $userId;
        $this->participantId = $participantId;
        $this->email = $email;
        $this->createdAt = $createdAt;
        $this->hasHappeningParticipation = $hasHappeningParticipation;
        $this->hasPaidParticipation = $hasPaidParticipation;
        $this->participantProductId = $participantProductId;
        $this->attributableProducts = $attributableProducts;
        $this->registrationData = $registrationData;
        $this->daysChecking = $daysChecking;
        $this->happeningChecking = $happeningChecking;
        $this->viewedSheets = $viewedSheets;
        $this->clickedElements = $clickedElements;
        $this->requestedMeetings = $requestedMeetings;
        $this->scheduledMeetings = $scheduledMeetings;
        $this->chatSessionsCallVisio = $chatSessionsCallVisio;
    }
}
