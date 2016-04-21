<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command;

use Proximum\Vimeet\Application\Exception\Asset\GuidelineAssetBuildFailedException;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildEventGuidelineAssetCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start building guideline assets');

        $eventId = $input->getOption('event');
        $events  = [];
        $event   = null;

        if ($eventId !== null) {
            $event = $this->getContainer()->get('vimeet_infrastructure.repository.event_repository')->getById($eventId);

            if ($event === null) {
                $output->writeln(sprintf('The event for the id %s was not found, the guideline assets will not be built', $eventId));
            } else {
                $output->writeln(sprintf('Building guideline assets for the event %s with id %s', $event->getTitle(), $eventId));
            }
        } else {
            $events = $this->getContainer()->get('vimeet_infrastructure.repository.event_repository')->getAll();
        }

        if (!empty($events)) {
            foreach ($events as $event) {
                $this->buildAsset($output, $event);
            }
        }

        if (null !== $event) {
            $this->buildAsset($output, $event);
        }

        $output->writeln('Guideline assets built');
    }

    /**
     * @param OutputInterface $output
     * @param Event $event
     */
    private function buildAsset(OutputInterface $output, Event $event)
    {
        try {
            $assetPath = $this->getContainer()->get('guideline.generator')->generate($event);
            $event->setAssetPath($assetPath);
            $this->getContainer()->get('vimeet_infrastructure.repository.event_repository')->set($event) ;
        } catch (GuidelineAssetBuildFailedException $ex) {
            $output->writeln('Could not build the asset for the event %s with the id %s', $event->getTitle(), $event->getId());
        }
    }
}
