<?php

namespace Proximum\Vimeet\Application\View\Happening\Webinar;

use Proximum\Vimeet\Application\View\Happening\WebinarParticipantView;
use Proximum\Vimeet\Application\View\Happening\WebinarSpeakerView;
use Proximum\Vimeet\Domain\Time\TimeRangeView;
use DateTimeInterface;

class ViewerWebinarView extends WebinarView
{
    /** @var int */
    public $timeRemainingInSeconds;

    /** @var bool */
    public $isVideoWebinarAndHasLiveUrl;

    /** @var string|null */
    public $hlsUrl;

    /** @var bool */
    public $isWebinarHls;

    /**
     * @param WebinarSpeakerView[]     $speakers
     * @param WebinarParticipantView[] $participantViews
     */
    public function __construct(
        int $happeningId,
        int $currentUserId,
        string $happeningTitle,
        bool $isVideoWebinarAndHasLiveUrl,
        string $token,
        string $sessionId,
        string $apiKey,
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
            $happeningId,
            $currentUserId,
            $happeningTitle,
            $isVideoWebinarAndHasLiveUrl,
            $token,
            $sessionId,
            $apiKey,
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
        $this->hlsUrl = $hlsUrl;
        $this->isWebinarHls = $isWebinarHls;
    }
}
