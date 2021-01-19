<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command;

use Proximum\Vimeet\Application\Command\Sheet\ExportUploadedObjectsCommand;
use Proximum\Vimeet\Application\Command\Sheet\ExportUploadedObjectsCommandHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportUploadedObjectsBySheetsCommand extends Command
{
    public const NAME = 'vimeet:export:uploaded-objects-by-sheets';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var ExportUploadedObjectsCommandHandler */
    private $exportUploadedObjectsCommandHandler;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        AdminRepositoryInterface $adminRepository,
        SheetRepositoryInterface $sheetRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        ExportUploadedObjectsCommandHandler $exportUploadedObjectsCommandHandler
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->adminRepository = $adminRepository;
        $this->sheetRepository = $sheetRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->exportUploadedObjectsCommandHandler = $exportUploadedObjectsCommandHandler;
    }

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Export uploaded objects by sheets')
            ->addArgument('eventId', InputArgument::REQUIRED, 'Event ID')
            ->addArgument('extraDataId', InputArgument::REQUIRED, 'ExtraData ID')
            ->addArgument('adminId', InputArgument::REQUIRED, 'Admin ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $event = $this->eventRepository->getById($input->getArgument('eventId'));

        if (!$event instanceof Event) {
            throw new \InvalidArgumentException('Event not found.');
        }

        $extraData = $this->extraDataRepository->findById($input->getArgument('extraDataId'));

        if (!$extraData instanceof ExtraData) {
            throw new \InvalidArgumentException('Extra data not found.');
        }

        $admin = $this->adminRepository->findById($input->getArgument('adminId'));

        if (!$admin instanceof Admin) {
            throw new \InvalidArgumentException('Admin not found.');
        }

        $sheetIds = explode(',', $extraData->getValue());
        $sheets = $this->sheetRepository->findByIds($sheetIds);

        $this->exportUploadedObjectsCommandHandler->handle(
            new ExportUploadedObjectsCommand($sheets, $admin, $event)
        );
    }
}
