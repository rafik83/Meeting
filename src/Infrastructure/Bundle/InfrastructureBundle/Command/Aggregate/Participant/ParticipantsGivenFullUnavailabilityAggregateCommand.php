<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Aggregate\Participant;

use Proximum\Vimeet\Application\Command\Aggregate\Participant\FullUnavailabilityForGivenParticipants;
use Proximum\Vimeet\Application\Command\Aggregate\Participant\FullUnavailabilityForGivenParticipantsHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParticipantsGivenFullUnavailabilityAggregateCommand extends Command
{
    const NAME = 'vimeet:participant:aggregate-full-unavailability-for-given-participants';

    /** @var FullUnavailabilityForGivenParticipantsHandler */
    private $fullUnavailabilityForGivenParticipantsHandler;

    /**
     * @param FullUnavailabilityForGivenParticipantsHandler $fullUnavailabilityForGivenParticipantsHandler
     */
    public function __construct(FullUnavailabilityForGivenParticipantsHandler $fullUnavailabilityForGivenParticipantsHandler) {
        parent::__construct(self::NAME);

        $this->fullUnavailabilityForGivenParticipantsHandler = $fullUnavailabilityForGivenParticipantsHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Aggregate the given participant full unavailability flag')
            ->addArgument('participants', InputArgument::REQUIRED, 'participant ids separated by comma')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $participantIds = explode(',', $input->getArgument('participants'));

        $this->fullUnavailabilityForGivenParticipantsHandler->handle(
            new FullUnavailabilityForGivenParticipants($participantIds)
        );
    }
}
