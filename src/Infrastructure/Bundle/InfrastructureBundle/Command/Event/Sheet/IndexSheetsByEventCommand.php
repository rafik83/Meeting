<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Event\Sheet;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Event\Sheet\Index;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IndexSheetsByEventCommand extends Command
{
    const NAME = 'vimeet:event:index-sheets';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var CommandBusInterface */
    private $commandBus;

    /**
     * @param EventRepositoryInterface $eventRepository
     * @param CommandBusInterface      $commandBus
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        CommandBusInterface $commandBus
    )
    {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->commandBus      = $commandBus;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Index the sheet for the given event id')
            ->addArgument(
                'eventId',
                InputArgument::REQUIRED,
                'The id of the event which the sheets will be re-index for'
            );
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

        $output->writeln(sprintf(
            'Start the indexation of the sheets of the event %d %s.',
            $event->getId(),
            $event->getTitle()
        ));

        $this->commandBus->handle(new Index($event));

        $output->writeln('Indexation finished.');
    }
}
