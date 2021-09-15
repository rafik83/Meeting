<?php

namespace Proximum\Vimeet\Application\View\Happening\Webinar;

use DateTimeInterface;
use Proximum\Vimeet\Application\View\Happening\Notification\NotificationView;
use Proximum\Vimeet\Application\View\Happening\WebinarParticipantView;
use Proximum\Vimeet\Application\View\Happening\WebinarSpeakerView;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

abstract class AbstractWebinarView
{
    /** @var int */
    public $eventId;

    /** @var bool */
    public $isSpeaker = false;

    /** @var int */
    public $happeningId;

    /** @var string */
    public $happeningTitle;

    /** @var string */
    public $token;

    /** @var string|null */
    public $sessionId;

    /** @var string */
    public $apiKey;

    /** @var NotificationView */
    public $notification;

    /** @var TimeRangeView */
    public $slot;

    /** @var DateTimeInterface */
    public $currentTime;

    /** @var int */
    public $timeRemainingInSeconds;

    /** @var int */
    public $warningTimeRemainingInSeconds;

    /** @var int used to stop record automatically */
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

    public bool $pollAllowed;

    /** @var bool */
    public $isVideoWebinarAndHasLiveUrl;

    /** @var bool */
    public $isVideoWebinarAndHappeningIsEnded;

    /** @var int */
    public $questionsCount;

    /** @var int $viewersCount */
    public $viewersCount;

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
        ?string $sessionId,
        string $apiKey,
        NotificationView $notification,
        array $speakers,
        array $participantViews,
        TimeRangeView $slot,
        DateTimeInterface $currentTime,
        ?string $headerImage,
        ?string $liveUrl,
        bool $sidebarAllowed,
        bool $pollAllowed,
        int $questionsCount,
        bool $isVideoWebinarAndHappeningIsEnded,
        int $viewersCount,
        int $timeRemainingBeforeStartInSeconds
    ) {
        $this->eventId = $eventId;
        $this->happeningId = $happeningId;
        $this->currentUserId = $currentUserId;
        $this->happeningTitle = $happeningTitle;
        $this->token = $token;
        $this->sessionId = $sessionId;
        $this->apiKey = $apiKey;
        $this->notification = $notification;
        $this->slot = $slot;
        $this->currentTime = $currentTime;
        $this->headerImage = $headerImage;
        $this->speakers = $speakers;
        $this->participantViews = $participantViews;
        $this->liveUrl = $liveUrl;
        $this->sidebarAllowed = $sidebarAllowed;
        $this->pollAllowed = $pollAllowed;
        $this->isVideoWebinarAndHasLiveUrl = $isVideoWebinarAndHasLiveUrl;
        $this->isVideoWebinarAndHappeningIsEnded = $isVideoWebinarAndHappeningIsEnded;
        $this->questionsCount = $questionsCount;
        $this->viewersCount = $viewersCount;
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

    public function isVideoWebinarAndHappeningIsEnded(): bool
    {
        return $this->isVideoWebinarAndHappeningIsEnded;
    }
}
