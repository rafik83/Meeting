<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Cart;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

class CartManager
{
    /**
     * @var CartRowRepositoryInterface
     */
    private $cartRowRepository;

    /**
     * @param CartRowRepositoryInterface $cartRowRepository
     */
    public function __construct(CartRowRepositoryInterface $cartRowRepository)
    {
        $this->cartRowRepository = $cartRowRepository;
    }

    /**
     * @param Sheet $sheet
     *
     * @return Cart
     */
    public function getCart(Sheet $sheet)
    {
        return new Cart($sheet, $this->cartRowRepository->findBySheet($sheet));
    }

    /**
     * @param Cart $cart
     */
    public function save(Cart $cart)
    {
        // Save / add rows
        foreach ($cart->getRows() as $row) {
            if ($row->getId()) {
                $this->cartRowRepository->set($row);
            } else {
                $this->cartRowRepository->add($row);
            }
        }

        // Remove deleted rows
        $this->cartRowRepository->deleteWhereNotIn($cart->getSheet(), $cart->getRows());
    }
}
