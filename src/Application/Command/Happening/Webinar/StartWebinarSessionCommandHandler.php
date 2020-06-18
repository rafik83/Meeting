<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar;

use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Command\Scan\Happening\ScanHappening;
use Proximum\Vimeet\Application\Command\Scan\Happening\ScanHappeningHandler;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class StartWebinarSessionCommandHandler
{
    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var ScanHappeningHandler */
    private $scanHappeningHandler;

    public function __construct(
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        HappeningRepositoryInterface $happeningRepository,
        ScanHappeningHandler $scanHappeningHandler
    ) {
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->happeningRepository = $happeningRepository;
        $this->scanHappeningHandler = $scanHappeningHandler;
    }

    public function handle(StartWebinarSessionCommand $command)
    {
        $happening = $command->getHappening();

        if (!$happening->isWebinar() || $happening->hasWebinarSessionId()) {
            return;
        }

        $session = $this->videoConferenceAdapter->createSession();
        $sessionId = $session->getSessionId();

        $this->scanHappeningHandler->handle(new ScanHappening(
            $happening->getEvent(),
            $command->getUser(),
            $happening,
            new \DateTime()
        ));

        $happening->setWebinarSessionId($sessionId);
        $this->happeningRepository->set($happening);
    }
}
