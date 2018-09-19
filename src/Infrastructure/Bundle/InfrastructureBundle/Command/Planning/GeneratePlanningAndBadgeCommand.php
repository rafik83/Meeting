<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Planning;

use Proximum\Vimeet\Application\Command\Planning\ExportPlanningAndBadge;
use Proximum\Vimeet\Application\Command\Planning\ExportPlanningAndBadgeHandler;
use Proximum\Vimeet\Domain\Model\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GeneratePlanningAndBadgeCommand extends Command
{
    const NAME = 'vimeet:planning_badge:generate';

    /** @var ExportPlanningAndBadgeHandler */
    private $exportPlanningAndBadgeHandler;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    public function __construct(
        ExportPlanningAndBadgeHandler $exportPlanningAndBadgeHandler,
        ExtraDataRepositoryInterface $extraDataRepository
    ) {
        parent::__construct(self::NAME);

        $this->exportPlanningAndBadgeHandler = $exportPlanningAndBadgeHandler;
        $this->extraDataRepository = $extraDataRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generate html for the participants plannings and badges')
            ->addOption('sheetIdsExtraData', null, InputOption::VALUE_REQUIRED, 'id of the Extra Data that contains the ids of the sheets')
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
        if (null === $input->getOption('orderBy')
            || null === $input->getOption('sheetIdsExtraData')
            || null === $input->getOption('emailToNotify')
            || null === $input->getOption('locale')
        ) {
            $output->writeln('<error>The orderBy, sheetIdsExtraData, emailToNotify and locale options are mandatory and can not be null</error>');

            throw new \InvalidArgumentException(
                sprintf(
                    'The orderBy, sheetIdsExtraData, emailToNotify and locale options are mandatory and can not be null, arguments passed: orderBy=%s types=%s emailToNotify=%s locale=%s',
                    $input->getOption('orderBy'),
                    $input->getOption('sheetIdsExtraData'),
                    $input->getOption('emailToNotify'),
                    $input->getOption('locale')
                )
            );
        }

        $sheetIdsExtraDataId = (int) $input->getOption('sheetIdsExtraData');
        $sheetIdsExtraData = $this->extraDataRepository->findById($sheetIdsExtraDataId);

        if (!$sheetIdsExtraData instanceof ExtraData) {
            throw new \InvalidArgumentException('The sheetIdsExtraData does not exist');
        }

        $this->exportPlanningAndBadgeHandler->handle(
            new ExportPlanningAndBadge(
                explode(',', $sheetIdsExtraData->getValue()),
                $input->getOption('orderBy'),
                $input->getOption('emailToNotify'),
                $input->getOption('locale')
            )
        );
    }
}
