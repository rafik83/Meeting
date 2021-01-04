<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Rooming\Export;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Rooming\Export\ExportRoomingList;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportRoomingListCommand extends Command
{
    public const NAME = 'vimeet:rooming:export-list';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        AdminRepositoryInterface $adminRepository,
        CommandBusInterface $commandBus
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->adminRepository = $adminRepository;
        $this->commandBus = $commandBus;
    }

    public function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Export rooming list of given event')
            ->addArgument('eventId', InputArgument::REQUIRED, 'Event ID')
            ->addArgument('adminId', InputArgument::REQUIRED, 'Admin ID')
            ->addArgument('locale', InputArgument::REQUIRED, 'Locale');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $eventId = $input->getArgument('eventId');
        $event = $this->eventRepository->getById($eventId);

        if (!$event instanceof Event) {
            throw new \InvalidArgumentException('Event not found.');
        }

        $adminId = $input->getArgument('adminId');
        $admin = $this->adminRepository->findById($adminId);

        if (!$admin instanceof Admin) {
            throw new \InvalidArgumentException('Admin not found.');
        }

        $locale = $input->getArgument('locale');

        $this->commandBus->handle(new ExportRoomingList($event, $admin, $locale));
    }
}
