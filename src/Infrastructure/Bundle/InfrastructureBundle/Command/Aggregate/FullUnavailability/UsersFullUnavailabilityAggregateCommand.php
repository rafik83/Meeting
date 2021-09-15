<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Aggregate\FullUnavailability;

use Proximum\Vimeet\Application\Command\Aggregate\Participant\FullUnavailabilityForGivenUsersInEvent;
use Proximum\Vimeet\Application\Command\Aggregate\Participant\FullUnavailabilityForGivenUsersInEventHandler;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UsersFullUnavailabilityAggregateCommand extends Command
{
    const NAME = 'vimeet:aggregate-full-unavailability:users';

    /** @var FullUnavailabilityForGivenUsersInEventHandler */
    private $fullUnavailabilityForGivenUsersInEventHandler;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /**
     * @param EventRepositoryInterface                      $eventRepository
     * @param FullUnavailabilityForGivenUsersInEventHandler $fullUnavailabilityForGivenUsersInEventHandler
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        FullUnavailabilityForGivenUsersInEventHandler $fullUnavailabilityForGivenUsersInEventHandler
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->fullUnavailabilityForGivenUsersInEventHandler = $fullUnavailabilityForGivenUsersInEventHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Aggregate the given participant full unavailability flag')
            ->addArgument('eventId', InputArgument::REQUIRED, 'Event id')
            ->addArgument('userIds', InputArgument::REQUIRED, 'User ids separated by comma')
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

        $userIds = explode(',', $input->getArgument('userIds'));

        $this->fullUnavailabilityForGivenUsersInEventHandler->handle(
            new FullUnavailabilityForGivenUsersInEvent($event, $userIds)
        );
    }
}
