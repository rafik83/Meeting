<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Cart;

use Proximum\Vimeet\Domain\Model\Cart;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\CartRepositoryInterface;

class CartManager
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param Sheet $sheet
     *
     * @return Cart
     */
    public function findOrCreateCart(Sheet $sheet)
    {
        $cart = $this->cartRepository->findBySheet($sheet);

        if ($cart === null) {
            $cart = new Cart([], $sheet->getTypePackageTemplate(), $sheet, new \DateTime());
            $this->cartRepository->add($cart);

            return $cart;
        }

        if ($cart->getTemplate() !== $sheet->getTypePackageTemplate()) {
            $this->cartRepository->delete($cart);

            $cart = new Cart([], $sheet->getTypePackageTemplate(), $sheet, new \DateTime());
            $this->cartRepository->add($cart);
        }

        return $cart;
    }
}
