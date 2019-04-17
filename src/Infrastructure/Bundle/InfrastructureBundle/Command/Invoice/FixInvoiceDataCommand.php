<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Invoice;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Query\Invoice\InvoiceDataQuery;
use Proximum\Vimeet\Application\Query\Invoice\InvoiceDataQueryHandler;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FixInvoiceDataCommand extends Command
{
    public const NAME = 'vimeet:invoice:fix-data';

    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /** @var Merger */
    private $orderMerger;

    /** @var InvoiceDataQueryHandler */
    private $invoiceDataQueryHandler;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var InvoiceRepositoryInterface */
    private $invoiceRepository;

    public function __construct(
        SerializerAdapterInterface $serializerAdapter,
        EventRepositoryInterface $eventRepository,
        InvoiceDataQueryHandler $invoiceDataQueryHandler,
        SheetRepositoryInterface $sheetRepository,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        parent::__construct(self::NAME);

        $this->serializerAdapter = $serializerAdapter;
        $this->eventRepository = $eventRepository;
        $this->orderMerger = new Merger();
        $this->invoiceDataQueryHandler = $invoiceDataQueryHandler;
        $this->sheetRepository = $sheetRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Fix invoice data attribute for an event')
            ->addArgument('eventId', InputArgument::REQUIRED, 'Event id')
            ->addOption('force', null, InputOption::VALUE_NONE, 'save changes');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event = $this->eventRepository->getById($input->getArgument('eventId'));
        $force = $input->getOption('force');

        if (null === $event) {
            throw new \InvalidArgumentException('Event not found.');
        }

        $sheets = $this->sheetRepository->findEnabledByEvent($event);

        $invoiceCount = 0;
        $toUpdateInvoiceCount = 0;
        foreach ($sheets as $sheet) {
            $invoices = $this->invoiceRepository->findBySheet($sheet);

            foreach ($invoices as $invoice) {
                $regenerateData = $this->regenerateInvoiceData($invoice);

                $invoiceCount++;
                if ($this->hasToBeUpdated($invoice, $regenerateData)) {
                    $toUpdateInvoiceCount++;
                    $output->writeln(sprintf('update %s', $invoice->getId()));

                    if ($force) {
                        $invoice->updateData($regenerateData);
                        $this->invoiceRepository->set($invoice);
                    }
                }
            }
        }

        $output->writeln(sprintf('Invoice : %s, updates : %s', $invoiceCount, $toUpdateInvoiceCount));
    }

    private function regenerateInvoiceData(Invoice $invoice): string
    {
        $orderMerged = $this->orderMerger->merge($invoice->getOrders());

        $invoiceDataView = $this->invoiceDataQueryHandler->handle(
            new InvoiceDataQuery(
                $orderMerged->getSheet(),
                $orderMerged,
                $orderMerged->getSheet()->getEvent()->getFallback()
            )
        );

        return $this->serializerAdapter->serialize($invoiceDataView, 'json');
    }

    /**
     * Anonymize email in case of developer environment
     *
     * @param Invoice $invoice
     * @param string  $regenerateData
     *
     * @return bool
     */
    protected function hasToBeUpdated(Invoice $invoice, string $regenerateData): bool
    {
        $regenerateArray = json_decode($regenerateData, true);
        $regenerateArray['billingInfosView']['email'] = null;
        $invoiceArray = json_decode($invoice->getData(), true);
        $invoiceArray['billingInfosView']['email'] = null;

        return json_encode($regenerateArray) !== json_encode($invoiceArray);
    }
}
