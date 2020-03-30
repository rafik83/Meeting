<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar;

use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\View\Happening\WebinarView;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class GetWebinarViewCommandHandler
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

    public function handle(GetWebinarViewCommand $command): WebinarView
    {
        $happening = $command->getHappening();
        $isSpeaker = $happening->hasSpeaker($command->getUser());

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
            $happening->getTitle($command->getLocale()),
            $token,
            $sessionId,
            $this->videoConferenceAdapter->getApiKey(),
            $isSpeaker,
            new TimeRangeView($happening->getBegin(), $happening->getEnd()),
            new \DateTime()
        );
    }
}
