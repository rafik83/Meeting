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
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeAlreadyExistException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeNotFoundException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeOutDatedException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeSoldOutException;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class AddHandler
{
    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * @var PromotionCodeRepositoryInterface
     */
    private $promotionCodeRepository;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * AddHandler constructor.
     *
     * @param CartManager                      $cartManager
     * @param PromotionCodeRepositoryInterface $promotionCodeRepository
     * @param \DateTimeInterface               $datetime
     */
    public function __construct(
        CartManager $cartManager,
        PromotionCodeRepositoryInterface $promotionCodeRepository,
        \DateTimeInterface $datetime
    ) {
        $this->cartManager             = $cartManager;
        $this->promotionCodeRepository = $promotionCodeRepository;
        $this->datetime                = $datetime;
    }

    /**
     * @param Add $add
     *
     * @throws PromotionCodeNotFoundException
     * @throws PromotionCodeOutDatedException
     * @throws PromotionCodeSoldOutException
     * @throws PromotionCodeAlreadyExistException
     */
    public function handle(Add $add)
    {
        $cart = $this->cartManager->getCart($add->sheet);

        $promotionCode = $this->promotionCodeRepository->findByEventAndCode(
            $add->sheet->getEvent(),
            $add->promotionCode
        );

        if (null === $promotionCode) {
            throw new PromotionCodeNotFoundException();
        }
        
        $cart->apply($promotionCode, $this->datetime);

        $this->cartManager->save($cart);
    }
}
