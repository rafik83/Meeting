<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Invoice;

use Proximum\Vimeet\Application\Command\Invoice\BatchGenerateInvoice;
use Proximum\Vimeet\Application\Command\Invoice\BatchGenerateInvoiceHandler;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateInvoiceCommand extends Command
{
    const NAME = 'vimeet:invoice:generate';

    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var BatchGenerateInvoiceHandler */
    private $batchGenerateInvoiceHandler;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /**
     * @param AdminRepositoryInterface    $adminRepository
     * @param EventRepositoryInterface    $eventRepository
     * @param BatchGenerateInvoiceHandler $batchGenerateInvoiceHandler
     */
    public function __construct(
        AdminRepositoryInterface $adminRepository,
        EventRepositoryInterface $eventRepository,
        BatchGenerateInvoiceHandler $batchGenerateInvoiceHandler
    ) {
        parent::__construct(self::NAME);

        $this->adminRepository             = $adminRepository;
        $this->batchGenerateInvoiceHandler = $batchGenerateInvoiceHandler;
        $this->eventRepository             = $eventRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generate invoice with given sheet ids')
            ->addArgument('eventId', InputArgument::REQUIRED, 'Event id')
            ->addArgument('adminId', InputArgument::REQUIRED, 'Admin id')
            ->addArgument('sheetIds', InputArgument::REQUIRED, 'Sheet ids separated by a comma');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $admin = $this->adminRepository->findById($input->getArgument('adminId'));
        $event = $this->eventRepository->getById($input->getArgument('eventId'));

        if (null === $admin) {
            throw new \Exception('Admin not found.');
        }

        if (null === $event) {
            throw new \Exception('Event not found.');
        }

        $sheetIds = explode(',', $input->getArgument('sheetIds'));

        $this->batchGenerateInvoiceHandler->handle(new BatchGenerateInvoice($event, $sheetIds, $admin));
    }
}
