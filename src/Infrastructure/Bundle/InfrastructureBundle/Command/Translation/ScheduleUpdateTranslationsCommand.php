<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Translation;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScheduleUpdateTranslationsCommand extends Command
{
    const NAME = 'vimeet:translations:schedule-update';

    /** @var JobQueueInterface */
    private $jobQueue;

    public function __construct(JobQueueInterface $jobQueue)
    {
        parent::__construct(self::NAME);
        $this->jobQueue = $jobQueue;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Schedule update translations')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->jobQueue->downloadTranslations();
        $output->writeln('<info>Translations update scheduled!</info>');
    }
}
