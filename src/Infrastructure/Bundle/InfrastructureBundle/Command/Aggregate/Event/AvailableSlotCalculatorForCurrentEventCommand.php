<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Aggregate\Event;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AvailableSlotCalculatorForCurrentEventCommand extends Command
{
    private const NAME = 'vimeet:event:available-slots-calculator-for-current-event';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var JobQueueInterface */
    private $jobQueue;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param EventRepositoryInterface $eventRepository
     * @param JobQueueInterface        $jobQueue
     * @param \DateTimeInterface       $dateTime
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        JobQueueInterface $jobQueue,
        \DateTimeInterface $dateTime
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->jobQueue = $jobQueue;
        $this->dateTime = $dateTime;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Prepare command to aggregate the slots available for the current events')
        ;
    }


    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $events = $this->eventRepository->getEventThatOccursDuringTheGivenDay($this->dateTime);

        if (empty($events)) {
            $output->writeln('No event occur now, aborted');

            return;
        }

        foreach ($events as $event) {
            $output->writeln(
                sprintf('Prepare the calculating of available slots for the event %s with id %s', $event->getTitle(), $event->getId())
            );
            $this->jobQueue->aggregateAvailableSlot($event);
        }
    }
}
