<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Package;

use Proximum\Vimeet\Domain\Cart\Cart;

class ProductByParticipantGetter
{
    /**
     * @param Cart $cart
     *
     * @return array of participantId => Product
     */
    public function getFromCart(Cart $cart): array
    {
        $participantsProduct = [];
        $sheet = $cart->getSheet();

        // Get product by participant from Cart
        foreach ($cart->getParticipantRows() as $cartRow) {
            foreach ($cartRow->getParticipants() as $participant) {
                $participantsProduct[$participant->getId()] = $cartRow->getProduct();
            }
        }

        // Set product to null or Product (from previous order) to others participants
        foreach ($sheet->getParticipantsArray() as $participant) {
            if (!isset($participantsProduct[$participant->getId()])) {
                $participantsProduct[$participant->getId()] = $participant->getParticipantProduct();
            }
        }

        // Set product to all participants if there only one participant product in the package
        $participantProducts = $sheet->getPackage()->getParticipants();

        if (1 === count($participantProducts)) {
            $participantProduct = reset($participantProducts);

            if (false !== $participantProduct) {
                foreach ($sheet->getParticipantsArray() as $participant) {
                    if (!isset($participantsProduct[$participant->getId()])) {
                        $participantsProduct[$participant->getId()] = $participantProduct;
                    }
                }
            }
        }

        return $participantsProduct;
    }
}
