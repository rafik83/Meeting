<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Cart;

use Proximum\Vimeet\Domain\Model\CartStep;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\CartStepRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRowRepositoryInterface;

class CartManager
{
    /**
     * @var CartRowRepositoryInterface
     */
    private $cartRowRepository;

    /**
     * @var CartStepRepositoryInterface
     */
    private $cartStepRepository;

    /**
     * @var PromotionCodeRowRepositoryInterface
     */
    private $promotionCodeRowRepository;

    /**
     * @param CartRowRepositoryInterface          $cartRowRepository
     * @param CartStepRepositoryInterface         $cartStepRepository
     * @param PromotionCodeRowRepositoryInterface $promotionCodeRowRepository
     */
    public function __construct(
        CartRowRepositoryInterface $cartRowRepository,
        CartStepRepositoryInterface $cartStepRepository,
        PromotionCodeRowRepositoryInterface $promotionCodeRowRepository
    ) {
        $this->cartRowRepository          = $cartRowRepository;
        $this->cartStepRepository         = $cartStepRepository;
        $this->promotionCodeRowRepository = $promotionCodeRowRepository;
    }

    /**
     * @param Sheet $sheet
     * @param int   $currentStep
     *
     * @return Cart
     */
    public function getCart(Sheet $sheet, $currentStep = null)
    {
        return new Cart(
            $sheet,
            $this->cartRowRepository->findBySheet($sheet),
            $this->promotionCodeRowRepository->findBySheet($sheet),
            $currentStep
        );
    }

    /**
     * @param Sheet $sheet
     */
    public function updateParticipantsQuantity(Sheet $sheet)
    {
        $cart = $this->getCart($sheet, null);

        if ($cart->getPlanRow()) {
            $cart->resolveParticipantsQuantity();
            $this->save($cart);
        }
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

        // Save / add promotion code rows
        foreach ($cart->getPromotionCodeRows() as $promotionCodeRow) {
            if ($promotionCodeRow->getId()) {
                // Remove promotionCodeRow if the discount is not usable anymore
                if (0 > $cart->getDiscount($promotionCodeRow->getPromotionCode())) {
                    $this->promotionCodeRowRepository->set($promotionCodeRow);
                } else {
                    $this->promotionCodeRowRepository->delete($promotionCodeRow);
                }
            } else {
                $this->promotionCodeRowRepository->add($promotionCodeRow);
            }
        }

        // Remove deleted rows
        $this->cartRowRepository->deleteWhereNotIn($cart->getSheet(), $cart->getRows());

        // Increment current step
        $cartStep = $this->cartStepRepository->findBySheet($cart->getSheet());

        if (null !== $cart->getCurrentStep()
            && null !== $cartStep
            && $cartStep->getCurrentStep() === $cart->getCurrentStep()
        ) {
            $cartStep->setCurrentStep($cartStep->getCurrentStep() + 1);
            $this->cartStepRepository->set($cartStep);
        } elseif (null !== $cart->getCurrentStep() && null === $cartStep) {
            $cartStep = new CartStep($cart->getSheet(), 2);
            $this->cartStepRepository->add($cartStep);
        }
    }

    /**
     * @param Cart $cart
     */
    public function deleteCartStep(Cart $cart)
    {
        $this->cartStepRepository->deleteForSheet($cart->getSheet());
    }
}
