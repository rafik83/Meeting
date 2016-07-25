<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Domain\Repository\Order\RowRepositoryInterface;

class UpdateRowHandler
{
    /**
     * @var RowRepositoryInterface
     */
    private $rowRepository;

    /**
     * @param RowRepositoryInterface $rowRepository
     */
    public function __construct(RowRepositoryInterface $rowRepository)
    {
        $this->rowRepository = $rowRepository;
    }

    /**
     * @param UpdateRow $updateRow
     */
    public function handle(UpdateRow $updateRow)
    {
        $updateRow->row->update($updateRow->label, $updateRow->price, $updateRow->quantity);
        $this->rowRepository->set($updateRow->row);
    }
}
