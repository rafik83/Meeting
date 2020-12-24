<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Aggregate\Sheet;

use Proximum\Vimeet\Application\Command\Sheet\Aggregate\AvailableSlotAggregator;
use Proximum\Vimeet\Application\Command\Sheet\Aggregate\AvailableSlotAggregatorHandler;
use Proximum\Vimeet\Application\Command\Sheet\Aggregate\SheetsAvailableSlotAggregator;
use Proximum\Vimeet\Application\Command\Sheet\Aggregate\SheetsAvailableSlotAggregatorHandler;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AvailableSlotCalculatorCommand extends Command
{
    const NAME = 'vimeet:sheet:available-slots-calculator';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var AvailableSlotAggregatorHandler */
    private $availableSlotAggregatorHandler;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var SheetsAvailableSlotAggregatorHandler */
    private $sheetsAvailableSlotAggregatorHandler;

    /**
     * @param EventRepositoryInterface             $eventRepository
     * @param SheetRepositoryInterface             $sheetRepository
     * @param SheetsAvailableSlotAggregatorHandler $sheetsAvailableSlotAggregatorHandler
     * @param AvailableSlotAggregatorHandler       $availableSlotAggregatorHandler
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        SheetRepositoryInterface $sheetRepository,
        SheetsAvailableSlotAggregatorHandler $sheetsAvailableSlotAggregatorHandler,
        AvailableSlotAggregatorHandler $availableSlotAggregatorHandler
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->availableSlotAggregatorHandler = $availableSlotAggregatorHandler;
        $this->sheetRepository = $sheetRepository;
        $this->sheetsAvailableSlotAggregatorHandler = $sheetsAvailableSlotAggregatorHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Aggregate the slots available for the sheets of the event')
            ->addOption(
                'sheet',
                null,
                InputOption::VALUE_OPTIONAL,
                'If set, the available slots will be calculated only for the given sheet'
            )
            ->addOption(
                'event',
                null,
                InputOption::VALUE_OPTIONAL,
                'If set, the available slots will be calculate only for the sheets of the given event id'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventId = $input->getOption('event');
        $sheetId = $input->getOption('sheet');

        if (null !== $sheetId) {
            $sheet = $this->sheetRepository->getSheetById($sheetId);

            if ($sheet instanceof Sheet) {
                $output->writeln(sprintf(
                    'Calculating available slots for the sheet %s with id %s',
                    $sheet->getTitle(),
                    $sheetId
                ));
                $this->availableSlotAggregatorHandler->handle(new AvailableSlotAggregator($sheet));
            }

            return;
        }

        if (null !== $eventId) {
            $event = $this->eventRepository->getById($eventId);

            if (null === $event) {
                $output->writeln(sprintf('The event for the id %s was not found', $eventId));

                return;
            }

            $output->writeln(sprintf('Calculating available slots for the event %s with id %s', $event->getTitle(), $eventId));
            $this->sheetsAvailableSlotAggregatorHandler->handle(new SheetsAvailableSlotAggregator($event));

            return;
        }

        $events = $this->eventRepository->getAll();

        foreach ($events as $event) {
            $output->writeln(sprintf('Calculating available slots for the event %s with id %s', $event->getTitle(), $eventId));
            $this->sheetsAvailableSlotAggregatorHandler->handle(new SheetsAvailableSlotAggregator($event));
        }
    }
}
