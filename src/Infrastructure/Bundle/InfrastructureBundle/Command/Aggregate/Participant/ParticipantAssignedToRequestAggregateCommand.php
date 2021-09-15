<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Aggregate\Participant;

use Proximum\Vimeet\Application\Command\Aggregate\Participant\AssignedToRequest;
use Proximum\Vimeet\Application\Command\Aggregate\Participant\AssignedToRequestHandler;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParticipantAssignedToRequestAggregateCommand extends Command
{
    const NAME = 'vimeet:participant:aggregate-assigned-to-request';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var AssignedToRequestHandler */
    private $assignedToRequestHandler;

    /**
     * @param EventRepositoryInterface $eventRepository
     * @param AssignedToRequestHandler $assignedToRequestHandler
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        AssignedToRequestHandler $assignedToRequestHandler
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository          = $eventRepository;
        $this->assignedToRequestHandler = $assignedToRequestHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Aggregate the participant assigned to request flag')
            ->addArgument('event', InputArgument::REQUIRED, 'Event id')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event = $this->eventRepository->getById($input->getArgument('event'));

        if (null === $event) {
            throw new \Exception('Event not found.');
        }

        $this->assignedToRequestHandler->handle(new AssignedToRequest($event));
    }
}
