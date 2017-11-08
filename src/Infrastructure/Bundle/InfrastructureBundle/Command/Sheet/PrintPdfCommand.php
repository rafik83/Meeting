<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Sheet;

use Proximum\Vimeet\Application\Command\Sheet\BatchPdf;
use Proximum\Vimeet\Application\Command\Sheet\BatchPdfHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PrintPdfCommand extends Command
{
    const NAME = 'vimeet:sheet:print:pdf';

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
            ->addOption('sheetIds', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Sheets')
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
        ) {
            $output->writeln('<error>The sheets ids, emailToNotify and locale options are mandatory and can not be null</error>');

            throw new \InvalidArgumentException(
                sprintf(
                    'The sheets ids, emailToNotify and locale options are mandatory and can not be null, arguments passed: sheetsIds=%s emailToNotify=%s locale=%s',
                    join(', ', $input->getOption('sheetIds')),
                    $input->getOption('emailToNotify'),
                    $input->getOption('locale')
                )
            );
        }

        $this->batchPdfHandler->handle(
            new BatchPdf(
                $input->getOption('sheetIds'),
                $input->getOption('emailToNotify'),
                $input->getOption('locale')
            )
        );
    }
}
