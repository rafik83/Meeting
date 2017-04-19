<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Planner;

use Proximum\Vimeet\Application\Command\Planner\Export;
use Proximum\Vimeet\Application\Command\Planner\ExportHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportPlannerCommand extends Command
{
    const NAME                      = 'vimeet:planner:export';
    const LOCK_MEETING_REQUEST      = 'lock';
    const DONT_LOCK_MEETING_REQUEST = 'not_lock';

    /** @var ExportHandler */
    private $exportPlannerHandler;

    /**
     * ExportPlannerCommand constructor.
     *
     * @param ExportHandler $exportPlannerHandler
     */
    public function __construct(ExportHandler $exportPlannerHandler)
    {
        parent::__construct(self::NAME);

        $this->exportPlannerHandler = $exportPlannerHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generate xml file for planner export')
            ->addArgument('event', InputArgument::REQUIRED, 'Event id')
            ->addArgument('admin_email', InputArgument::REQUIRED, 'Admin email to notify')
            ->addArgument('locale', InputArgument::REQUIRED, 'Locale for the email')
            ->addArgument('solutionType', InputArgument::REQUIRED, 'Solution type to prepare for algorithm')
            ->addArgument(
                'lockMeetingRequest',
                InputArgument::OPTIONAL,
                'Should meeting request be locked after export',
                self::DONT_LOCK_MEETING_REQUEST
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->exportPlannerHandler->handle(
            new Export(
                $input->getArgument('event'),
                $input->getArgument('locale'),
                $input->getArgument('admin_email'),
                $input->getArgument('lockMeetingRequest'),
                $input->getArgument('solutionType')
            )
        );
    }
}
