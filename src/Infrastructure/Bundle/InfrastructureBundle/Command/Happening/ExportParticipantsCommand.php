<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Happening;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Happening\Export\Export;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportParticipantsCommand extends Command
{
    const NAME = 'vimeet:happening:participants:export';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var AdminRepositoryInterface */
    private $adminRepository;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        AdminRepositoryInterface $adminRepository,
        CommandBusInterface $commandBus
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->commandBus = $commandBus;
        $this->adminRepository = $adminRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Export the happening participants')
            ->addArgument('event', InputArgument::REQUIRED, 'The event to export')
            ->addArgument('admin', InputArgument::REQUIRED, 'The admin id who requested the export')
            ->addArgument('locale', InputArgument::REQUIRED, 'Locale of the export')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event = $this->eventRepository->getById($input->getArgument('event'));
        $admin = $this->adminRepository->findById($input->getArgument('admin'));

        if (null === $admin) {
            throw new \InvalidArgumentException('Admin not found.');
        }

        if (null === $event) {
            throw new \InvalidArgumentException('Event not found.');
        }

        $locale = $event->getAvailableLocale($input->getArgument('locale'));

        $this->commandBus->handle(new Export($event, $admin, $locale));
    }
}
