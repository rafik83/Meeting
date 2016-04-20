<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildEventGuidelineAssetCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('vimeet:event:build-guideline-asset')
            ->setDescription('Build guideline asset for the events')
            ->addOption(
                'event',
                null,
                InputOption::VALUE_OPTIONAL,
                'If set, the asset will be build only for the given event id'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start building guideline assets');

        $eventId = $input->getOption('event');

        if ($eventId !== null) {
            $events = $this->getContainer()->get('vimeet_infrastructure.repository.event_repository')->getById($eventId);

            if ($events === null) {
                $output->writeln('The event for the given id was not found, the guideline assets will not be built');
            }
        } else {
            $events = $this->getContainer()->get('vimeet_infrastructure.repository.event_repository')->getAll();
        }

        if ($events) {
            foreach ($events as $event) {
                $assetPath = $this->getContainer()->get('guideline.generator')->generate($event);
                $event->setAssetPath($assetPath);
                $this->getContainer()->get('vimeet_infrastructure.repository.event_repository')->set($event);
            }
        }

        $output->writeln('Guideline assets built');
    }
}
