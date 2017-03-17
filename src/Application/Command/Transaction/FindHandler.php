<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Transaction;

use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;

class FindHandler
{
    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;
    
    /**
     * FindHandler constructor.
     *
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }
    
    /**
     * @param Find $command
     */
    public function handle(Find $command)
    {
        $transactions = $this->transactionRepository->findPaidByDateRangeAndCrossEvent(
            $command->beginDate,
            $command->endDate
        );
    }
    
}
