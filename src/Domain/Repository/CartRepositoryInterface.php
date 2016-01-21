<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Cart;
use Proximum\Vimeet\Domain\Model\Sheet;

interface CartRepositoryInterface
{
    /**
     * @param Cart $cart
     */
    public function add(Cart $cart);

    /**
     * @param Cart $cart
     */
    public function set(Cart $cart);

    /**
     * @param Sheet $sheet
     *
     * @return Cart
     */
    public function findBySheet(Sheet $sheet);

    /**
     * @param Cart $cart
     */
    public function delete(Cart $cart);
}
