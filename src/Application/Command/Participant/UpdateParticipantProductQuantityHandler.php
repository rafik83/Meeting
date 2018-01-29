<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Components\Package\ProductByParticipantGetter;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class UpdateParticipantProductQuantityHandler
{
    /** @var CartManager */
    private $cartManager;

    /** @var ProductByParticipantGetter */
    private $productByParticipantGetter;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @param CartManager                $cartManager
     * @param ProductByParticipantGetter $productByParticipantGetter
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        CartManager $cartManager,
        ProductByParticipantGetter $productByParticipantGetter,
        ProductRepositoryInterface $productRepository
    ) {
        $this->cartManager = $cartManager;
        $this->productByParticipantGetter = $productByParticipantGetter;
        $this->productRepository = $productRepository;
    }

    /**
     * @param UpdateParticipantProductQuantity $command
     */
    public function handle(UpdateParticipantProductQuantity $command)
    {
        $product = $this->productRepository->findById($command->productId);

        if ($product === null) {
            throw new \InvalidArgumentException('Product not found');
        }

        $cart = $this->cartManager->getCart($command->sheet);
        $productParticipants = $this->productByParticipantGetter->getFromCart($cart);

        $productParticipants[$command->participant->getId()] = $product;

        $this->cartManager->updateParticipantsQuantity($cart, $productParticipants);
        $this->cartManager->save($cart);
    }
}
