<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\Reconciliate;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReconciliationCommand extends Command
{
    public const NAME = 'vimeet:happening:webinar:reconciliate-record';

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        CommandBusInterface $commandBus
    ) {
        parent::__construct(self::NAME);

        $this->commandBus = $commandBus;
        $this->happeningRepository = $happeningRepository;
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
            return 0;
        }

        $this->commandBus->handle(new Reconciliate($happening));

        return 0;
    }
}
