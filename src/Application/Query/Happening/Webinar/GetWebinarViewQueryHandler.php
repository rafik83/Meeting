<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar;

use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\View\Happening\WebinarView;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class GetWebinarViewQueryHandler
{
    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(VideoConferenceAdapterInterface $videoConferenceAdapter, \DateTimeInterface $dateTime)
    {
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->dateTime = $dateTime;
    }

    public function handle(GetWebinarViewQuery $query): WebinarView
    {
        $happening = $query->getHappening();
        $isSpeaker = $happening->hasSpeaker($query->getUser());

        if (!$happening->hasWebinarSessionId()) {
            throw new \LogicException('Happening webinar session id not created');
        }

        $session = $this->videoConferenceAdapter->getSession($happening->getWebinarSessionId());

        $token = $this->videoConferenceAdapter->generateAccessToken(
            $session,
            $happening->getEnd(),
            [],
            $isSpeaker
        );

        $sessionId = $session->getSessionId();

        return new WebinarView(
            $happening->getTitle($query->getLocale()),
            $token,
            $sessionId,
            $this->videoConferenceAdapter->getApiKey(),
            $isSpeaker,
            new TimeRangeView($happening->getBegin(), $happening->getEnd()),
            $this->dateTime,
            $happening->getWebinarHeaderImage($query->getLocale())
        );
    }
}
