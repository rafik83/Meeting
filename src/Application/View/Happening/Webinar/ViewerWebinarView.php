<?php

namespace Proximum\Vimeet\Application\View\Happening\Webinar;

use DateTimeInterface;
use Proximum\Vimeet\Application\View\Happening\Notification\NotificationView;
use Proximum\Vimeet\Application\View\Happening\WebinarParticipantView;
use Proximum\Vimeet\Application\View\Happening\WebinarSpeakerView;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class ViewerWebinarView extends AbstractWebinarView
{
    /** @var int */
    public $timeRemainingInSeconds;

    /** @var WaitingMediaView */
    public $waitingMediaView;

    /** @var bool */
    public $isVideoWebinarAndHasLiveUrl;

    /** @var bool */
    public $isWebinarHls;

    /** @var string|null */
    public $hlsUrl;

    public string $programUrl;

    public string $voteUrl;

    public bool $hasToVote;
    public bool $canAdminPoll = false;

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
        int $timeRemainingInSeconds,
        ?string $headerImage,
        WaitingMediaView $waitingMediaView,
        ?string $liveUrl,
        bool $sidebarAllowed,
        bool $pollAllowed,
        bool $isVideoWebinarAndHappeningIsEnded,
        int $questionsCount,
        bool $isWebinarHls,
        ?string $hlsUrl,
        int $viewersCount,
        int $timeRemainingBeforeStartInSeconds,
        string $programUrl,
        string $voteUrl,
        bool $hasToVote
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
            $pollAllowed,
            $questionsCount,
            $isVideoWebinarAndHappeningIsEnded,
            $viewersCount,
            $timeRemainingBeforeStartInSeconds
        );

        $this->timeRemainingInSeconds = $timeRemainingInSeconds;
        $this->waitingMediaView = $waitingMediaView;
        $this->isVideoWebinarAndHasLiveUrl = $isVideoWebinarAndHasLiveUrl;
        $this->isWebinarHls = $isWebinarHls;
        $this->hlsUrl = $hlsUrl;
        $this->programUrl = $programUrl;
        $this->voteUrl = $voteUrl;
        $this->hasToVote = $hasToVote;
    }

    public function hasWaitingMedia(): bool
    {
        return null !== $this->waitingMediaView->type;
    }
}
