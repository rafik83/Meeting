<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar;

use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class StartWebinarSessionCommandHandler
{
    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    public function __construct(
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        HappeningRepositoryInterface $happeningRepository
    ) {
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->happeningRepository = $happeningRepository;
    }

    public function handle(StartWebinarSessionCommand $command): void
    {
        $happening = $command->getHappening();

        if (!$happening->isWebinar() || $happening->hasWebinarSessionId()) {
            return;
        }

        $session = $this->videoConferenceAdapter->createSession();
        $sessionId = $session->getSessionId();

        $happening->setWebinarSessionId($sessionId);
        $this->happeningRepository->set($happening);
    }
}
