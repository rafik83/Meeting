<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Planning;

use Proximum\Vimeet\Application\Command\Planning\ExportPlanning;
use Proximum\Vimeet\Application\Command\Planning\ExportPlanningHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GeneratePlanningCommand extends Command
{
    const NAME = 'vimeet:planning:generate';

    /** @var ExportPlanningHandler */
    private $exportPlanningHandler;

    /**
     * GeneratePlanningCommand constructor.
     *
     * @param ExportPlanningHandler $exportPlanningHandler
     */
    public function __construct(ExportPlanningHandler $exportPlanningHandler)
    {
        parent::__construct(self::NAME);
        $this->exportPlanningHandler = $exportPlanningHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generate html for the participants plannings')
            ->addOption('types', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Type')
            ->addOption('orderBy', null, InputOption::VALUE_REQUIRED, 'OrderBy Sheet name or participant last name')
            ->addOption('emailToNotify', null, InputOption::VALUE_REQUIRED, 'email to notify at the end of the command')
            ->addOption('locale', null, InputOption::VALUE_REQUIRED, 'locale for the mail of notification')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('orderBy') === null
            || empty($input->getOption('types'))
            || null === $input->getOption('emailToNotify')
            || null === $input->getOption('locale')
        ) {
            $output->writeln('<error>The orderBy, types, emailToNotify and locale options are mandatory and can not be null</error>');

            throw new \InvalidArgumentException(
                sprintf(
                    'The orderBy, types, emailToNotify and locale options are mandatory and can not be null, arguments passed: orderBy=%s types=%s emailToNotify=%s locale=%s',
                    $input->getOption('orderBy'),
                    join(', ', $input->getOption('types')),
                    $input->getOption('emailToNotify'),
                    $input->getOption('locale')
                )
            );
        }

        $this->exportPlanningHandler->handle(
            new ExportPlanning(
                $input->getOption('types'),
                $input->getOption('orderBy'),
                $input->getOption('emailToNotify'),
                $input->getOption('locale')
            )
        );
    }
}
