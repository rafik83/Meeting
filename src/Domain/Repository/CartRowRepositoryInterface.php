<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Sheet;

interface CartRowRepositoryInterface
{
    /**
     * @param CartRow $cartRow
     */
    public function add(CartRow $cartRow);

    /**
     * @param CartRow $cartRow
     */
    public function delete(CartRow $cartRow);

    /**
     * @param Sheet $sheet
     *
     * @return CartRow[]
     */
    public function findBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return CartRow
     */
    public function findCartRowPlanBySheet(Sheet $sheet);
}
