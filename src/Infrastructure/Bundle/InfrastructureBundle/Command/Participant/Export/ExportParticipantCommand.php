<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Participant\Export;

use Proximum\Vimeet\Application\Command\Participant\Export\Export;
use Proximum\Vimeet\Application\Command\Participant\Export\ExportHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportParticipantCommand extends Command
{
    public const NAME = 'vimeet:participant:export';

    /** @var ExportHandler */
    private $exportHandler;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /**
     * @param ExportHandler                $exportHandler
     * @param EventRepositoryInterface     $eventRepository
     * @param ExtraDataRepositoryInterface $extraDataRepository
     * @param AdminRepositoryInterface     $adminRepository
     */
    public function __construct(
        ExportHandler $exportHandler,
        EventRepositoryInterface $eventRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        AdminRepositoryInterface $adminRepository
    ) {
        parent::__construct(self::NAME);

        $this->exportHandler = $exportHandler;
        $this->extraDataRepository = $extraDataRepository;
        $this->adminRepository = $adminRepository;
        $this->eventRepository = $eventRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generate html for the participants plannings')
            ->addOption('eventId', null, InputOption::VALUE_REQUIRED, 'Event id')
            ->addOption('extraDataWithParticipantIds', null, InputOption::VALUE_REQUIRED, 'id of the Extra Data that contains the ids of the participants')
            ->addOption('adminId', null, InputOption::VALUE_REQUIRED, 'admin to notify at the end of the command')
            ->addOption('locale', null, InputOption::VALUE_REQUIRED, 'locale for the export')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (null === $input->getOption('eventId')
            || null === $input->getOption('extraDataWithParticipantIds')
            || null === $input->getOption('adminId')
            || null === $input->getOption('locale')
        ) {
            $output->writeln('<error>The eventId, participantIdsExtraData, adminId and locale options are mandatory and can not be null</error>');

            throw new \InvalidArgumentException(
                sprintf(
                    'The eventId, participantIdsExtraData, adminId and locale options are mandatory and can not be null, arguments passed: eventId=%s extraDataWithParticipantIds=%s adminId=%s locale=%s',
                    $input->getOption('eventId'),
                    $input->getOption('extraDataWithParticipantIds'),
                    $input->getOption('adminId'),
                    $input->getOption('locale')
                )
            );
        }

        $participantIdsExtraDataId = (int) $input->getOption('extraDataWithParticipantIds');
        $participantIdsExtraData = $this->extraDataRepository->findById($participantIdsExtraDataId);

        $event = $this->eventRepository->getById($input->getOption('eventId'));
        $admin = $this->adminRepository->findById($input->getOption('adminId'));

        if (!$participantIdsExtraData instanceof ExtraData) {
            throw new \InvalidArgumentException('The participantIdsExtraData does not exist');
        }

        if (!$admin instanceof Admin) {
            throw new \InvalidArgumentException('The admin does not exist');
        }

        if (!$event instanceof Event) {
            throw new \InvalidArgumentException('The event does not exist');
        }

        $command = new Export(
            $event,
            explode(',', $participantIdsExtraData->getValue()),
            $admin,
            $input->getOption('locale')
        );

        $this->exportHandler->handle($command);

        $output->writeln('<info>Memory peak usage: '.memory_get_peak_usage().'</info>');
    }
}
