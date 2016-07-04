<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package\PromotionCode;

use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeNotFoundException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeOutDatedException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeSoldOutException;

class AddHandler
{
    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * AddHandler constructor.
     *
     * @param CartManager $cartManager
     */
    public function __construct(CartManager $cartManager)
    {

        $this->cartManager = $cartManager;
    }

    /**
     * @param Add $add
     * 
     * @throws PromotionCodeNotFoundException
     * @throws PromotionCodeOutDatedException
     * @throws PromotionCodeSoldOutException
     */
    public function handle(Add $add)
    {
        $this->cartManager->apply($add->sheet, $add->promotionCodeForm->promotionCode);
    }
}
