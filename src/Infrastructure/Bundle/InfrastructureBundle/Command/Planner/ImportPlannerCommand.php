<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Planner;

use Proximum\Vimeet\Application\Command\Planner\Import;
use Proximum\Vimeet\Application\Command\Planner\ImportHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportPlannerCommand extends Command
{
    const NAME = 'vimeet:planner:import';

    /** @var ImportHandler */
    private $importPlannerHandler;

    /**
     * ImportPlannerCommand constructor.
     *
     * @param ImportHandler $importPlannerHandler
     */
    public function __construct(ImportHandler $importPlannerHandler)
    {
        parent::__construct(self::NAME);

        $this->importPlannerHandler = $importPlannerHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Import xml planner file for algorithm')
            ->addArgument('event', InputArgument::REQUIRED, 'Event id')
            ->addArgument('admin_email', InputArgument::REQUIRED, 'Admin email to notify')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->importPlannerHandler->handle(
            new Import(
                $input->getArgument('event'),
                $input->getArgument('admin_email')
            )
        );
    }
}
