<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Sheet;

use Proximum\Vimeet\Application\Command\Sheet\BatchPdf;
use Proximum\Vimeet\Application\Command\Sheet\BatchPdfHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PrintPdfCommand extends Command
{
    const NAME = 'vimeet:sheet:print-pdf';

    /** @var BatchPdfHandler */
    private $batchPdfHandler;

    /**
     * @param BatchPdfHandler $batchPdfHandler
     */
    public function __construct(BatchPdfHandler $batchPdfHandler)
    {
        parent::__construct(self::NAME);
        $this->batchPdfHandler = $batchPdfHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generate html for the participants sheet')
            ->addOption('sheetIds', null, InputOption::VALUE_REQUIRED, 'Sheets')
            ->addOption('eventId', null, InputOption::VALUE_REQUIRED, 'Event id')
            ->addOption('emailToNotify', null, InputOption::VALUE_REQUIRED, 'email to notify at the end of the command')
            ->addOption('locale', null, InputOption::VALUE_REQUIRED, 'locale for the mail of notification')
            ->addOption('orderBy', null, InputOption::VALUE_REQUIRED, 'the order of the sheets')
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
            || null === $input->getOption('orderBy')
        ) {
            $output->writeln('<error>The sheets ids, emailToNotify, locale and orderBy options are mandatory and can not be null</error>');

            throw new \InvalidArgumentException(
                sprintf(
                    'The sheets ids, emailToNotify and locale options are mandatory and can not be null, arguments passed: eventId=%s sheetsIds=%s emailToNotify=%s locale=%s orderBy=%s',
                    $input->getOption('eventId'),
                    $input->getOption('sheetIds'),
                    $input->getOption('emailToNotify'),
                    $input->getOption('locale'),
                    $input->getOption('orderBy')
                )
            );
        }

        $this->batchPdfHandler->handle(
            new BatchPdf(
                $input->getOption('eventId'),
                explode(',', $input->getOption('sheetIds')),
                $input->getOption('emailToNotify'),
                $input->getOption('locale'),
                $input->getOption('orderBy')
            )
        );
    }
}
