<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Aggregate\FullUnavailability;

use Proximum\Vimeet\Application\Command\Aggregate\Participant\FullUnavailability;
use Proximum\Vimeet\Application\Command\Aggregate\Participant\FullUnavailabilityHandler;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UsersFullUnavailabilityByEventAggregateCommand extends Command
{
    const NAME = 'vimeet:aggregate-full-unavailability:event';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var FullUnavailabilityHandler */
    private $fullUnavailabilityHandler;

    /**
     * @param EventRepositoryInterface  $eventRepository
     * @param FullUnavailabilityHandler $fullUnavailabilityHandler
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        FullUnavailabilityHandler $fullUnavailabilityHandler
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository           = $eventRepository;
        $this->fullUnavailabilityHandler = $fullUnavailabilityHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Aggregate the participant full unavailability flag')
            ->addArgument('eventId', InputArgument::REQUIRED, 'Event id')
            ->addArgument('onlyCatalog', inputArgument::OPTIONAL, 'Only the participant in catalog')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event = $this->eventRepository->getById($input->getArgument('eventId'));

        if (null === $event) {
            throw new \Exception('Event not found.');
        }

        $onlyCatalog = 1 === $input->getArgument('onlyCatalog');

        $this->fullUnavailabilityHandler->handle(new FullUnavailability($event, $onlyCatalog));
    }
}
