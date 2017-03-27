<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command;

use Proximum\Vimeet\Application\Command\Invoice\BatchGenerateInvoice;
use Proximum\Vimeet\Application\Command\Invoice\BatchGenerateInvoiceHandler;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
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

    /**
     * @param AdminRepositoryInterface    $adminRepository
     * @param BatchGenerateInvoiceHandler $batchGenerateInvoiceHandler
     */
    public function __construct(
        AdminRepositoryInterface $adminRepository,
        BatchGenerateInvoiceHandler $batchGenerateInvoiceHandler
    ) {
        parent::__construct(self::NAME);

        $this->adminRepository             = $adminRepository;
        $this->batchGenerateInvoiceHandler = $batchGenerateInvoiceHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generate invoice with given sheet ids')
            ->addArgument('adminId', InputArgument::REQUIRED, 'Admin id')
            ->addArgument('sheetIds', InputArgument::REQUIRED, 'Sheet ids separated by a comma')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $admin = $this->adminRepository->findById($input->getArgument('adminId'));

        if (null === $admin) {
            throw new \Exception('Admin not found.');
        }

        $sheetIds = explode(',', $input->getArgument('sheetIds'));

        $this->batchGenerateInvoiceHandler->handle(new BatchGenerateInvoice($sheetIds, $admin));
    }
}
