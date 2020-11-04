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

    /** @var bool */
    public $isVideoWebinarAndHasLiveUrl;

    /** @var bool */
    public $isWebinarHls;

    /** @var string|null */
    public $hlsUrl;

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
        ?string $headerImage,
        ?string $liveUrl,
        bool $sidebarAllowed,
        bool $isVideoWebinarAndHappeningIsEnded,
        bool $isWebinarHls,
        ?string $hlsUrl
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
            $isVideoWebinarAndHappeningIsEnded
        );

        $this->timeRemainingInSeconds = $timeRemainingInSeconds;
        $this->isVideoWebinarAndHasLiveUrl = $isVideoWebinarAndHasLiveUrl;
        $this->isWebinarHls = $isWebinarHls;
        $this->hlsUrl = $hlsUrl;
    }
}
