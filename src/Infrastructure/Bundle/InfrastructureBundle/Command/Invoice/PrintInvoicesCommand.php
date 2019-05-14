<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Invoice;

use Proximum\Vimeet\Application\Command\Sheet\BatchPrintInvoices;
use Proximum\Vimeet\Application\Command\Sheet\BatchPrintInvoicesHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PrintInvoicesCommand extends Command
{
    public const NAME = 'vimeet:invoice:print';

    /** @var BatchPrintInvoicesHandler */
    private $batchPrintInvoicesHandler;

    public function __construct(BatchPrintInvoicesHandler $batchPrintInvoicesHandler)
    {
        parent::__construct(self::NAME);
        $this->batchPrintInvoicesHandler = $batchPrintInvoicesHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Print invoices with given sheet ids')
            ->addOption('sheetIds', null, InputOption::VALUE_REQUIRED, 'Sheets')
            ->addOption('eventId', null, InputOption::VALUE_REQUIRED, 'Event id')
            ->addOption('emailToNotify', null, InputOption::VALUE_REQUIRED, 'email to notify at the end of the command')
            ->addOption('locale', null, InputOption::VALUE_REQUIRED, 'locale for the mail of notification')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (empty($input->getOption('sheetIds'))
            || null === $input->getOption('emailToNotify')
            || null === $input->getOption('locale')
            || null === $input->getOption('eventId')
        ) {
            $output->writeln(
                '<error>The sheets ids, emailToNotify and locale options are mandatory and can not be null</error>'
            );

            throw new \InvalidArgumentException(
                sprintf(
                    'The sheets ids, emailToNotify and locale options are mandatory and can not be null, arguments passed: eventId=%s sheetsIds=%s emailToNotify=%s locale=%s',
                    $input->getOption('eventId'),
                    $input->getOption('sheetIds'),
                    $input->getOption('emailToNotify'),
                    $input->getOption('locale')
                )
            );
        }

        $this->batchPrintInvoicesHandler->handle(
            new BatchPrintInvoices(
                $input->getOption('eventId'),
                explode(',', $input->getOption('sheetIds')),
                $input->getOption('emailToNotify'),
                $input->getOption('locale')
            )
        );
    }
}
