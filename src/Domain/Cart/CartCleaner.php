<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Cart;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

/**
 * Remove other event product from cart
 * Fix issue jira#1375
 */
class CartCleaner
{
    /** @var CartManager */
    private $cartManager;

    /** @var CartRowRepositoryInterface */
    private $cartRowRepository;

    /**
     * @param CartManager                $cartManager
     * @param CartRowRepositoryInterface $cartRowRepository
     */
    public function __construct(CartManager $cartManager, CartRowRepositoryInterface $cartRowRepository)
    {
        $this->cartManager = $cartManager;
        $this->cartRowRepository = $cartRowRepository;
    }

    /**
     * @param Sheet $sheet
     */
    public function handle(Sheet $sheet)
    {
        $cart = $this->cartManager->getCart($sheet);

        foreach ($cart->getRows() as $cartRow) {
            if ($cartRow->getProduct()->getEvent()->getId() !== $sheet->getEvent()->getId()) {
                $this->cartRowRepository->delete($cartRow);
            }
        }
    }
}
