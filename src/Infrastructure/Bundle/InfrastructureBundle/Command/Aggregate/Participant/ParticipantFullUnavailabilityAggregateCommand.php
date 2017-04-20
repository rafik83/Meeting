<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Aggregate\Participant;

use Proximum\Vimeet\Application\Command\Aggregate\Participant\FullUnavailability;
use Proximum\Vimeet\Application\Command\Aggregate\Participant\FullUnavailabilityHandler;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParticipantFullUnavailabilityAggregateCommand extends Command
{
    const NAME = 'vimeet:participant:aggregate-full-unavailability';

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
            ->addArgument('event', InputArgument::REQUIRED, 'Event id')
            ->addArgument('onlyCatalog', inputArgument::OPTIONAL, 'Only the participant in catalog')
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
        $onlyCatalog = $input->getArgument('onlyCatalog') !== null && $input->getArgument('onlyCatalog') === 1;

        $this->fullUnavailabilityHandler->handle(new FullUnavailability($event, $onlyCatalog));
    }
}
