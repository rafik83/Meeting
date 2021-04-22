<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\Reconciliate;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReconciliationCommand extends Command
{
    public const NAME = 'vimeet:happening:webinar:reconciliate-record';

    private CommandBusInterface $commandBus;
    private HappeningRepositoryInterface $happeningRepository;
    private LoggerInterface $logger;

    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        CommandBusInterface $commandBus,
        LoggerInterface $logger
    ) {
        parent::__construct(self::NAME);

        $this->commandBus = $commandBus;
        $this->happeningRepository = $happeningRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Reconciliate the record archive of the happening webinar')
            ->addArgument('happening', InputArgument::REQUIRED, 'The happening to reconciliate')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $happening = $this->happeningRepository->getById($input->getArgument('happening'));

        if (null === $happening) {
            throw new \InvalidArgumentException('Happening not found.');
        }

        if (!$happening->isWebinarRecorded()) {
            $this->logger->error(sprintf('Webinar %d is not set to be recorded, abort reconciliation', $happening->getId()));
            return 0;
        }

        $this->commandBus->handle(new Reconciliate($happening));

        return 0;
    }
}
