<?php

namespace Proximum\Vimeet\Application\View\Happening\Webinar;

use DateTimeInterface;
use Proximum\Vimeet\Application\View\Happening\Notification\NotificationView;
use Proximum\Vimeet\Application\View\Happening\WebinarParticipantView;
use Proximum\Vimeet\Application\View\Happening\WebinarSpeakerView;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class SpeakerWebinarView extends AbstractWebinarView
{
    /** @var bool */
    public $isSpeaker = true;

    /** @var int */
    public $timeRemainingInSeconds;

    /** @var int */
    public $warningTimeRemainingInSeconds;

    /** @var bool */
    public $isVideoWebinarAndHasLiveUrl;

    /** @var bool */
    public $isWebinarRecorded;

    /** @var bool */
    public $isWebinarRecording;

    /** @var bool */
    public $isWebinarRecordAutoStart;

    /** @var bool */
    public $allowWebinarOnHLS;

    /** @var bool */
    public $isStreamOpenToPublic;

    /** @var bool */
    public $canDeleteChatMessage;

    /** @var bool */
    public $canDeleteQuestionMessage;

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
        array $speakers,
        array $participantViews,
        TimeRangeView $slot,
        DateTimeInterface $currentTime,
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
        bool $isWebinarRecordAutoStart,
        bool $isStreamOpenToPublic,
        int $questionsCount = 0,
        bool $allowWebinarOnHLS = false,
        int $viewersCount = 0,
        bool $canDeleteChatMessage = false,
        bool $canDeleteQuestionMessage = false
    ) {
        parent::__construct(
            $eventId,
            $happeningId,
            $currentUserId,
            $happeningTitle,
            $isVideoWebinarAndHasLiveUrl,
            $token,
            $sessionId,
            $apiKey,
            $notification,
            $speakers,
            $participantViews,
            $slot,
            $currentTime,
            $headerImage,
            $liveUrl,
            $sidebarAllowed,
            $questionsCount,
            $isVideoWebinarAndHappeningIsEnded,
            $viewersCount,
            $timeRemainingBeforeStartInSeconds
        );

        $this->timeRemainingInSeconds = $timeRemainingInSeconds;
        $this->stopTimestamp = $stopTimestamp;
        $this->warningTimeRemainingInSeconds = $warningTimeRemainingInSeconds;
        $this->isWebinarRecorded = $isWebinarRecorded;
        $this->isWebinarRecording = $isWebinarRecording;
        $this->isWebinarRecordAutoStart = $isWebinarRecordAutoStart;
        $this->allowWebinarOnHLS = $allowWebinarOnHLS;
        $this->isStreamOpenToPublic = $isStreamOpenToPublic;
        $this->viewersCount = $viewersCount;
        $this->canDeleteChatMessage = $canDeleteChatMessage;
        $this->canDeleteQuestionMessage = $canDeleteQuestionMessage;
    }
}
