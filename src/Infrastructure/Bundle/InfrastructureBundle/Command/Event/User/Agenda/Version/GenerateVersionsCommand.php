<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Event\User\Agenda\Version;

use Proximum\Vimeet\Application\Command\Event\User\Agenda\Version\GenerateVersions;
use Proximum\Vimeet\Application\Command\Event\User\Agenda\Version\GenerateVersionsHandler;
use Proximum\Vimeet\Application\Exception\Event\User\Agenda\Version\VersionsAlreadyGenerated;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateVersionsCommand extends Command
{
    const NAME = 'vimeet:event:generate-user-agenda-versions';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var GenerateVersionsHandler */
    private $generateVersionsHandler;

    /**
     * @param EventRepositoryInterface $eventRepository
     * @param GenerateVersionsHandler  $generateVersionsHandler
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        GenerateVersionsHandler $generateVersionsHandler
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->generateVersionsHandler = $generateVersionsHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generate version of agenda for the users of the given event')
            ->addArgument('eventId', InputArgument::REQUIRED, 'Event id')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $event = $this->eventRepository->getById($input->getArgument('eventId'));

        if (null === $event) {
            throw new \InvalidArgumentException('Event not found.');
        }

        if ($event->isUserAgendaVersionsGenerated()) {
            throw new VersionsAlreadyGenerated('The versions for this event are already generated');
        }

        $this->generateVersionsHandler->handle(new GenerateVersions($event));
    }
}
