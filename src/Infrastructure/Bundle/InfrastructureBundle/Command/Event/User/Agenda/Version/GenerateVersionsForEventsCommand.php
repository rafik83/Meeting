<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Event\User\Agenda\Version;

use Proximum\Vimeet\Application\Command\Event\User\Agenda\Version\GenerateVersions;
use Proximum\Vimeet\Application\Command\Event\User\Agenda\Version\GenerateVersionsHandler;
use Proximum\Vimeet\Application\Exception\Event\User\Agenda\Version\VersionsAlreadyGenerated;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateVersionsForEventsCommand extends Command
{
    public const NAME = 'vimeet:event:generate-agenda-versions-for-events';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var GenerateVersionsHandler */
    private $generateVersionsHandler;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param EventRepositoryInterface $eventRepository
     * @param GenerateVersionsHandler  $generateVersionsHandler
     * @param \DateTimeInterface       $dateTime
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        GenerateVersionsHandler $generateVersionsHandler,
        \DateTimeInterface $dateTime
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->generateVersionsHandler = $generateVersionsHandler;
        $this->dateTime = $dateTime;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generate version of agenda for the upcoming event with a past schedule published date')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $events = $this->eventRepository->findEventsWithPastSchedulePublishDateAndAgendaVersionsNotGenerated($this->dateTime);

        foreach ($events as $event) {
            if ($event->isUserAgendaVersionsGenerated()) {
                continue;
            }

            try {
                $output->writeln(sprintf('Generating user agenda versions for the event of id %d', $event->getId()));

                $this->generateVersionsHandler->handle(new GenerateVersions($event));
            } catch (VersionsAlreadyGenerated $exception) {
                continue;
            }
        }
    }
}
