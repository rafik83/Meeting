<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Clean;

use Proximum\Vimeet\Domain\Repository\AvailableSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemovePastAvailableSlotCommand extends Command
{
    public const NAME = 'vimeet:clean:past-available-slot';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var AvailableSlotRepositoryInterface */
    private $availableSlotRepository;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        AvailableSlotRepositoryInterface $availableSlotRepository,
        \DateTimeInterface $dateTime
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->dateTime = $dateTime;
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->availableSlotRepository = $availableSlotRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Clean the available_slot table for past events')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start the deletion of the past available slots of the past month');

        $pastMonthDate = clone $this->dateTime;
        $pastMonthDate = $pastMonthDate->modify('-1 month');

        $pastWeekDate = clone $this->dateTime;
        $pastWeekDate = $pastWeekDate->modify('-1 week');

        $events = $this->eventRepository->findEventsByDateRange($pastMonthDate, $pastWeekDate);
        $slots = $this->meetingSlotRepository->findSlotIdsByEvents($events);

        $this->availableSlotRepository->deleteForSlotIds($slots);
        $output->writeln('Done deleting');
    }
}
