<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Event;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Application\Command\Event\ToggleParticipantVisioCommand as ToggleParticipantVisioCommandForEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ToggleParticipantVisioCommand extends Command
{
    public const NAME = 'vimeet:toggle:participant_visio';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        CommandBusInterface $commandBus
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->commandBus = $commandBus;
    }

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Toggle participant visio for event')
            ->addArgument('eventId', InputArgument::REQUIRED, 'Event id')
            ->addArgument('visio', InputArgument::REQUIRED, 'Visio value');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('Toggle participant visio for event');

        $event = $this->eventRepository->getById($input->getArgument('eventId'));
        if (!$event instanceof Event) {
            return;
        }

        $this->commandBus->handle(new ToggleParticipantVisioCommandForEvent(
            $event,
            $input->getArgument('visio'))
        );
    }
}
