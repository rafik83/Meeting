<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\Comexposium\Webservice;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot\Export;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot\ExportHandler;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComexposiumExportSpotCommand extends Command
{
    public const NAME = 'vimeet:comexposium:export-spots';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var ExportHandler */
    private $exportHandler;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        AdminRepositoryInterface $adminRepository,
        ExportHandler $exportHandler
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->adminRepository = $adminRepository;
        $this->exportHandler = $exportHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Export sheets spot from Comexposium Webservice')
            ->addArgument('event', InputArgument::REQUIRED, 'The event to export')
            ->addArgument('admin', InputArgument::REQUIRED, 'The admin id who requested the export');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event = $this->eventRepository->getById($input->getArgument('event'));
        $admin = $this->adminRepository->findById($input->getArgument('admin'));

        if (null === $admin) {
            throw new \Exception('Admin not found.');
        }

        if (null === $event) {
            throw new \Exception('Event not found.');
        }

        $this->exportHandler->handle(new Export($event, $admin));
    }
}
