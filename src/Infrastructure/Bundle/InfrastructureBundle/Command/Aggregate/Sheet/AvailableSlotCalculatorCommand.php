<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Aggregate\Sheet;

use Proximum\Vimeet\Application\Command\Sheet\Aggregate\AvailableSlotAggregator;
use Proximum\Vimeet\Application\Command\Sheet\Aggregate\AvailableSlotAggregatorHandler;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AvailableSlotCalculatorCommand extends Command
{
    const NAME = 'vimeet:sheet:available-slots-calculator';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var AvailableSlotAggregatorHandler */
    private $availableSlotAggregatorHandler;

    /**
     * @param EventRepositoryInterface       $eventRepository
     * @param AvailableSlotAggregatorHandler $availableSlotAggregatorHandler
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        AvailableSlotAggregatorHandler $availableSlotAggregatorHandler
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->availableSlotAggregatorHandler = $availableSlotAggregatorHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Aggregate the slots available for the sheet of the event')
            ->addOption(
                'event',
                null,
                InputOption::VALUE_OPTIONAL,
                'If set, the available slots will be calculate only for the sheet of the given event id'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventId = $input->getOption('event');

        if ($eventId !== null) {
            $event = $this->eventRepository->getById($eventId);

            if ($event === null) {
                $output->writeln(sprintf('The event for the id %s was not found', $eventId));

                return;
            }

            $output->writeln(sprintf('Calculating available slots for the event %s with id %s', $event->getTitle(), $eventId));
            $this->availableSlotAggregatorHandler->handle(new AvailableSlotAggregator($event));

            return;
        }

        $events = $this->eventRepository->getAll();

        foreach ($events as $event) {
            $output->writeln(sprintf('Calculating available slots for the event %s with id %s', $event->getTitle(), $eventId));
            $this->availableSlotAggregatorHandler->handle(new AvailableSlotAggregator($event));
        }
    }
}
