<?php

namespace Proximum\Vimeet\Application\Components\Package;

use Proximum\Vimeet\Domain\Cart\Cart;

class ProductByParticipantCartGetter
{
    /**
     * This method returns the product of type participant indexed by the participant id
     * The participant product can be null
     * If there is only one product, this product is taken for all the participant
     *
     * @param Cart $cart
     *
     * @return array of participantId => Product
     */
    public function getFromCart(Cart $cart): array
    {
        $participantsProduct = [];

        $sheet = $cart->getSheet();

        $products = $sheet->getPackage()->getParticipants();

        // Get product by participant from Cart
        foreach ($cart->getParticipantRows() as $cartRow) {
            foreach ($cartRow->getParticipants() as $participant) {
                if (\in_array($cartRow->getProduct(), $products, true)) {
                    $participantsProduct[$participant->getId()] = $cartRow->getProduct();
                }
            }
        }

        // Set product to null or Product (from previous order) to others participants
        foreach ($sheet->getParticipantsArray() as $participant) {
            if (!isset($participantsProduct[$participant->getId()])) {
                $participantsProduct[$participant->getId()] = $participant->getParticipantProduct();
            }
        }

        return $participantsProduct;
    }
}
