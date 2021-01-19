<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Analytic\MeetingSolution;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Command\Analytic\MeetingSolution\Create;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateMeetingSolutionCommand extends Command
{
    const NAME = 'vimeet:analytic:generate-meeting-solution';

    /** @var CommandBus */
    private $commandBus;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /**
     * @param EventRepositoryInterface $eventRepository
     * @param CommandBus               $commandBus
     */
    public function __construct(EventRepositoryInterface $eventRepository, CommandBus $commandBus)
    {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->commandBus = $commandBus;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generate the meeting solution analytic of an planner solution')
            ->addArgument('eventId', InputArgument::REQUIRED, 'Event id');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event = $this->eventRepository->getById($input->getArgument('eventId'));

        if (null === $event) {
            throw new \InvalidArgumentException(
                sprintf('Event %s not found', $input->getArgument('eventId'))
            );
        }

        $this->commandBus->handle(new Create($event));
    }
}
