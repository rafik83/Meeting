<?php

namespace Proximum\Vimeet\Application\Command\Event\User\Agenda\Version;

use Proximum\Vimeet\Application\Exception\Event\User\Agenda\Version\VersionsAlreadyGenerated;
use Proximum\Vimeet\Domain\Model\User\Agenda\Version;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Agenda\VersionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\Generator;

class GenerateVersionsHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var VersionRepositoryInterface */
    private $versionRepository;

    /** @var Generator */
    private $generator;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /**
     * @param EventRepositoryInterface   $eventRepository
     * @param UserRepositoryInterface    $userRepository
     * @param VersionRepositoryInterface $versionRepository
     * @param Generator                  $generator
     * @param \DateTimeInterface         $dateTime
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        UserRepositoryInterface $userRepository,
        VersionRepositoryInterface $versionRepository,
        Generator $generator,
        \DateTimeInterface $dateTime
    ) {
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
        $this->versionRepository = $versionRepository;
        $this->generator = $generator;
        $this->dateTime = $dateTime;
    }

    /**
     * @param GenerateVersions $command
     *
     * @throws VersionsAlreadyGenerated
     */
    public function handle(GenerateVersions $command)
    {
        if ($command->event->isUserAgendaVersionsGenerated()) {
            throw new VersionsAlreadyGenerated(
                sprintf('The versions for the event %s are already generated', $command->event->getId())
            );
        }

        $users = $this->userRepository->findByEventAndInCatalog($command->event);

        foreach ($users as $user) {
            $version = $this->generator->generate($command->event, $user);

            $userAgendaVersion = new Version($command->event, $user, $version, $this->dateTime);

            $this->versionRepository->add($userAgendaVersion);
        }

        $command->event->setUserAgendaVersionsGenerated(true);
        $this->eventRepository->set($command->event);
    }
}
