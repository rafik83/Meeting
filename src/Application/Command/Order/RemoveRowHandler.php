<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Exception\Order\RemoveProductNotAllowedException;
use Proximum\Vimeet\Domain\Repository\Order\RowRepositoryInterface;

class RemoveRowHandler
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
     * @param RemoveRow $removeRow
     *
     * @throws RemoveProductNotAllowedException
     */
    public function handle(RemoveRow $removeRow)
    {
        if ($removeRow->row->isProduct()) {
            throw new RemoveProductNotAllowedException('Delete a product row is not allowed');
        }

        $this->rowRepository->remove($removeRow->row);
    }
}
