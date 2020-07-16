<?php

namespace Proximum\Vimeet\Application\View\Happening;

use Proximum\Vimeet\Domain\Time\TimeRangeView;

class WebinarView
{
    /** @var int */
    public $happeningId;

    /** @var string */
    public $happeningTitle;

    /** @var string */
    public $token;

    /** @var string */
    public $sessionId;

    /** @var string */
    public $apiKey;

    /** @var bool */
    public $isSpeaker;

    /** @var TimeRangeView */
    public $slot;

    /** @var \DateTimeInterface */
    public $currentTime;

    /** @var int */
    public $timeRemainingInSeconds;

    /** @var int */
    public $warningTimeRemainingInSeconds;

    /** @var string|null */
    public $headerImage;

    /** @var WebinarSpeakerView[] */
    public $speakers;

    /** @var WebinarParticipantView[] */
    public $participantViews;

    /** @var int */
    public $currentUserId;

    /** @var string|null */
    public $liveUrl;

    /** @var bool */
    public $isWebinarRecorded;

    /** @var string */
    public $webinarRecordStatus;

    /** @var int */
    public $timeRemainingBeforeStartInSeconds;

    /**
     * @param WebinarSpeakerView[]     $speakers
     * @param WebinarParticipantView[] $participantViews
     */
    public function __construct(
        int $happeningId,
        int $currentUserId,
        string $happeningTitle,
        string $token,
        string $sessionId,
        string $apiKey,
        bool $isSpeaker,
        array $speakers,
        array $participantViews,
        TimeRangeView $slot,
        \DateTimeInterface $currentTime,
        int $timeRemainingInSeconds,
        int $warningTimeRemainingInSeconds,
        int $timeRemainingBeforeStartInSeconds,
        ?string $headerImage,
        ?string $liveUrl,
        bool $isWebinarRecorded,
        string $isWebinarRecordStatus
    ) {
        $this->happeningId = $happeningId;
        $this->currentUserId = $currentUserId;
        $this->happeningTitle = $happeningTitle;
        $this->token = $token;
        $this->sessionId = $sessionId;
        $this->apiKey = $apiKey;
        $this->isSpeaker = $isSpeaker;
        $this->slot = $slot;
        $this->currentTime = $currentTime;
        $this->timeRemainingInSeconds = $timeRemainingInSeconds;
        $this->warningTimeRemainingInSeconds = $warningTimeRemainingInSeconds;
        $this->headerImage = $headerImage;
        $this->speakers = $speakers;
        $this->participantViews = $participantViews;
        $this->liveUrl = $liveUrl;
        $this->isWebinarRecorded = $isWebinarRecorded;
        $this->webinarRecordStatus = $isWebinarRecordStatus;
        $this->timeRemainingBeforeStartInSeconds = $timeRemainingBeforeStartInSeconds;
    }

    public function getSpeakerInfosByUserId(): string
    {
        $mapping = [];

        foreach ($this->speakers as $speaker) {
            $position = null !== $speaker->position ? '(' . $speaker->position . ')' : '';
            $organization = null !== $speaker->organization ? '- ' . $speaker->organization : '';
            $speakerInfo = sprintf(
                '%s %s %s %s',
                $speaker->firstName,
                $speaker->lastName,
                $position,
                $organization
            );

            $mapping[$speaker->userId] = $speakerInfo;
        }

        foreach ($this->participantViews as $participantView) {
            $position = null !== $participantView->position ? '(' . $participantView->position . ')' : '';
            $sheetTitle = null !== $participantView->sheetTitle ? '- ' . $participantView->sheetTitle : '';

            $mapping[$participantView->userId] = sprintf(
                '%s %s %s %s',
                $participantView->firstName,
                $participantView->lastName,
                $position,
                $sheetTitle
            );
        }

        return json_encode($mapping);
    }
}
