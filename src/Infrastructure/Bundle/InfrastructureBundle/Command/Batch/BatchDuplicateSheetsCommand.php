<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Batch;

use Proximum\Vimeet\Application\Command\Sheet\SheetDuplicator;
use Proximum\Vimeet\Application\Command\Sheet\SheetDuplicatorHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraData;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BatchDuplicateSheetsCommand extends Command
{
    public const NAME = 'vimeet:sheets:duplicate';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var SheetDuplicatorHandler */
    private $sheetDuplicatorHandler;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        AdminRepositoryInterface $adminRepository,
        SheetRepositoryInterface $sheetRepository,
        TypeRepositoryInterface $typeRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        SheetDuplicatorHandler $sheetDuplicatorHandler
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->adminRepository = $adminRepository;
        $this->sheetRepository = $sheetRepository;
        $this->typeRepository = $typeRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->sheetDuplicatorHandler = $sheetDuplicatorHandler;
    }

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Batch duplicate sheets action')
            ->addArgument('adminId', InputArgument::REQUIRED, 'Admin id')
            ->addArgument('typeId', InputArgument::REQUIRED, 'Type id')
            ->addArgument('extraDataId', InputArgument::REQUIRED, 'ExtraData id')
            ->addArgument('originalEventId', InputArgument::REQUIRED, 'Original event id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $admin = $this->adminRepository->findById($input->getArgument('adminId'));

        if (!$admin instanceof Admin) {
            throw new \InvalidArgumentException('Admin not found.');
        }

        $type = $this->typeRepository->getById($input->getArgument('typeId'));

        if (!$type instanceof Type) {
            throw new \InvalidArgumentException('Type not found.');
        }

        $extraData = $this->extraDataRepository->findById($input->getArgument('extraDataId'));

        if (!$extraData instanceof ExtraData) {
            throw new \InvalidArgumentException('Extra data not found.');
        }

        $originalEvent = $this->eventRepository->getById($input->getArgument('originalEventId'));

        if (!$originalEvent instanceof Event) {
            throw new \InvalidArgumentException('Event not found.');
        }

        $sheetIds = explode(',', $extraData->getValue());
        $sheets = $this->sheetRepository->findByIds($sheetIds);

        $this->sheetDuplicatorHandler->handle(
            new SheetDuplicator($originalEvent, $sheets, $admin, $type)
        );
    }
}
