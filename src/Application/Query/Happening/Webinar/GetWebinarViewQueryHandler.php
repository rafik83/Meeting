<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar;

use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\View\Happening\WebinarView;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class GetWebinarViewQueryHandler
{
    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        VideoConferenceAdapterInterface $videoConferenceAdapter
    ) {
        $this->happeningRepository = $happeningRepository;
        $this->videoConferenceAdapter = $videoConferenceAdapter;
    }

    public function handle(GetWebinarViewQuery $query): WebinarView
    {
        $happening = $query->getHappening();

        // @todo: check if happening has user as a speaker
        $isSpeaker = true;

        $session = $happening->hasWebinarSessionId()
            ? $this->videoConferenceAdapter->getSession($happening->getWebinarSessionId())
            : $this->videoConferenceAdapter->createSession();

        $token = $this->videoConferenceAdapter->generateAccessToken(
            $session,
            $happening->getEnd(),
            [],
            $isSpeaker
        );

        $sessionId = $session->getSessionId();

        if (!$happening->hasWebinarSessionId()) {
            $happening->setWebinarSessionId($sessionId);
            $this->happeningRepository->set($happening);
        }

        return new WebinarView(
            $token,
            $sessionId,
            $this->videoConferenceAdapter->getApiKey(),
            $isSpeaker,
            new TimeRangeView($happening->getBegin(), $happening->getEnd()),
            new \DateTime()
        );
    }
}
