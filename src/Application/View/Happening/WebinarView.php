<?php

namespace Proximum\Vimeet\Application\View\Happening;

use Proximum\Vimeet\Application\View\Happening\Notification\NotificationView;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class WebinarView
{
    /** @var int */
    public $eventId;

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

    /** @var NotificationView */
    public $notification;

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

    /** @var int $endTimestamp used to stop record automatically */
    public $stopTimestamp;

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
    public $sidebarAllowed;

    /** @var bool */
    public $isVideoWebinarAndHasLiveUrl;

    /** @var bool */
    public $isVideoWebinarAndHappeningIsEnded;

    /** @var bool */
    public $isWebinarRecorded;

    /** @var bool */
    public $isWebinarRecording;

    /** @var bool */
    public $isWebinarRecordAutoStart;

    /** @var int */
    public $timeRemainingBeforeStartInSeconds;

    /**
     * @param WebinarSpeakerView[]     $speakers
     * @param WebinarParticipantView[] $participantViews
     */
    public function __construct(
        int $eventId,
        int $happeningId,
        int $currentUserId,
        string $happeningTitle,
        bool $isVideoWebinarAndHasLiveUrl,
        string $token,
        string $sessionId,
        string $apiKey,
        NotificationView $notification,
        bool $isSpeaker,
        array $speakers,
        array $participantViews,
        TimeRangeView $slot,
        \DateTimeInterface $currentTime,
        int $timeRemainingInSeconds,
        int $warningTimeRemainingInSeconds,
        int $timeRemainingBeforeStartInSeconds,
        int $stopTimestamp,
        ?string $headerImage,
        ?string $liveUrl,
        bool $sidebarAllowed,
        bool $isVideoWebinarAndHappeningIsEnded,
        bool $isWebinarRecorded,
        bool $isWebinarRecording,
        bool $isWebinarRecordAutoStart
    ) {
        $this->eventId = $eventId;
        $this->happeningId = $happeningId;
        $this->currentUserId = $currentUserId;
        $this->happeningTitle = $happeningTitle;
        $this->token = $token;
        $this->sessionId = $sessionId;
        $this->apiKey = $apiKey;
        $this->notification = $notification;
        $this->isSpeaker = $isSpeaker;
        $this->slot = $slot;
        $this->currentTime = $currentTime;
        $this->timeRemainingInSeconds = $timeRemainingInSeconds;
        $this->timeRemainingBeforeStartInSeconds = $timeRemainingBeforeStartInSeconds;
        $this->stopTimestamp = $stopTimestamp;
        $this->warningTimeRemainingInSeconds = $warningTimeRemainingInSeconds;
        $this->headerImage = $headerImage;
        $this->speakers = $speakers;
        $this->participantViews = $participantViews;
        $this->liveUrl = $liveUrl;
        $this->sidebarAllowed = $sidebarAllowed;
        $this->isVideoWebinarAndHasLiveUrl = $isVideoWebinarAndHasLiveUrl;
        $this->isVideoWebinarAndHappeningIsEnded = $isVideoWebinarAndHappeningIsEnded;
        $this->isWebinarRecorded = $isWebinarRecorded;
        $this->isWebinarRecording = $isWebinarRecording;
        $this->isWebinarRecordAutoStart = $isWebinarRecordAutoStart;
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

    public function isVideoWebinarAndHappeningIsEnded(): bool
    {
        return $this->isVideoWebinarAndHappeningIsEnded;
    }
}
