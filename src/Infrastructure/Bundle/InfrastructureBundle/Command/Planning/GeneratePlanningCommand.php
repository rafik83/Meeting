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
use Proximum\Vimeet\Domain\Model\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GeneratePlanningCommand extends Command
{
    const NAME = 'vimeet:planning:generate';

    /** @var ExportPlanningHandler */
    private $exportPlanningHandler;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /**
     * @param ExportPlanningHandler        $exportPlanningHandler
     * @param ExtraDataRepositoryInterface $extraDataRepository
     */
    public function __construct(
        ExportPlanningHandler $exportPlanningHandler,
        ExtraDataRepositoryInterface $extraDataRepository
    ) {
        parent::__construct(self::NAME);

        $this->exportPlanningHandler = $exportPlanningHandler;
        $this->extraDataRepository = $extraDataRepository;
    }

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generate html for the participants plannings')
            ->addOption('sheetIdsExtraData', null, InputOption::VALUE_REQUIRED, 'id of the Extra Data that contains the ids of the sheets')
            ->addOption('orderBy', null, InputOption::VALUE_REQUIRED, 'OrderBy Sheet name or participant last name')
            ->addOption('emailToNotify', null, InputOption::VALUE_REQUIRED, 'email to notify at the end of the command')
            ->addOption('locale', null, InputOption::VALUE_REQUIRED, 'locale for the mail of notification')
            ->addOption('withBadge', null, InputOption::VALUE_REQUIRED, 'add badge to impression')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if (null === $input->getOption('orderBy')
            || null === $input->getOption('sheetIdsExtraData')
            || null === $input->getOption('emailToNotify')
            || null === $input->getOption('locale')
            || null === $input->getOption('withBadge')
        ) {
            $output->writeln('<error>The orderBy, sheetIdsExtraData, emailToNotify and locale options are mandatory and can not be null</error>');

            throw new \InvalidArgumentException(
                sprintf(
                    'The orderBy, sheetIdsExtraData, emailToNotify and locale options are mandatory and can not be null, arguments passed: orderBy=%s types=%s emailToNotify=%s locale=%s withBadge=%s',
                    $input->getOption('orderBy'),
                    $input->getOption('sheetIdsExtraData'),
                    $input->getOption('emailToNotify'),
                    $input->getOption('locale'),
                    $input->getOption('withBadge')
                )
            );
        }

        $sheetIdsExtraDataId = (int) $input->getOption('sheetIdsExtraData');
        $sheetIdsExtraData = $this->extraDataRepository->findById($sheetIdsExtraDataId);

        if (!$sheetIdsExtraData instanceof ExtraData) {
            throw new \InvalidArgumentException('The sheetIdsExtraData does not exist');
        }

        $this->exportPlanningHandler->handle(
            new ExportPlanning(
                explode(',', $sheetIdsExtraData->getValue()),
                $input->getOption('orderBy'),
                $input->getOption('emailToNotify'),
                $input->getOption('locale'),
                (bool)$input->getOption('withBadge')
            )
        );
    }
}
