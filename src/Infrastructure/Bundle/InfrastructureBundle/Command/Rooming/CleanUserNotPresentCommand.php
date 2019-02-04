<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Rooming;

use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanUserNotPresentCommand extends Command
{
    public const NAME = 'vimeet:rooming:clean-user-not-present';

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var StayRepositoryInterface */
    private $stayRepository;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        SheetRepositoryInterface $sheetRepository,
        StayRepositoryInterface $stayRepository
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->sheetRepository = $sheetRepository;
        $this->stayRepository = $stayRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Aggregate the slots available for the sheets of the event')
            ->addArgument('eventId', InputArgument::REQUIRED, 'Event id')
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

        $stays = $this->stayRepository->getStaysByEvent($event);

        foreach ($stays as $stay) {
            $countUsers = \count($stay->getUsers());
            $usersNotPresent = 0;

            foreach ($stay->getUsers() as $user) {
                $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

                if (empty($sheets)) {
                    $usersNotPresent++;

                    if ($countUsers === 1) {
                        $this->stayRepository->remove($stay);

                        continue 2;
                    }

                    if ($usersNotPresent === $countUsers) {
                        $this->stayRepository->remove($stay);

                        continue 2;
                    }
                }
            }
        }
    }
}
