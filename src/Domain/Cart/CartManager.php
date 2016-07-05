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
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeAlreadyExistException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeNotFoundException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeOutDatedException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeSoldOutException;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\CartStepRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;
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
     * @var PromotionCodeRepositoryInterface
     */
    private $promotionCodeRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var PromotionCodeRowRepositoryInterface
     */
    private $promotionCodeRowRepository;

    /**
     * @param CartRowRepositoryInterface          $cartRowRepository
     * @param CartStepRepositoryInterface         $cartStepRepository
     * @param PromotionCodeRepositoryInterface    $promotionCodeRepository
     * @param PromotionCodeRowRepositoryInterface $promotionCodeRowRepository
     * @param \DateTimeInterface                  $dateTime
     */
    public function __construct(
        CartRowRepositoryInterface $cartRowRepository,
        CartStepRepositoryInterface $cartStepRepository,
        PromotionCodeRepositoryInterface $promotionCodeRepository,
        PromotionCodeRowRepositoryInterface $promotionCodeRowRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->cartRowRepository          = $cartRowRepository;
        $this->cartStepRepository         = $cartStepRepository;
        $this->promotionCodeRepository    = $promotionCodeRepository;
        $this->promotionCodeRowRepository = $promotionCodeRowRepository;
        $this->dateTime                   = $dateTime;
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
                $this->promotionCodeRowRepository->set($promotionCodeRow);
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

    /**
     * @param Sheet  $sheet
     * @param string $code
     *
     * @throws PromotionCodeNotFoundException
     * @throws PromotionCodeOutDatedException
     * @throws PromotionCodeSoldOutException
     * @throws PromotionCodeAlreadyExistException
     */
    public function apply(Sheet $sheet, $code)
    {
        $promotionsCode = $this->promotionCodeRepository->findByEventAndCode(
            $sheet->getEvent(),
            $code
        );

        if (empty($promotionsCode)) {
            throw new PromotionCodeNotFoundException();
        }

        /** @var PromotionCode $promotionCode */
        $promotionCode = reset($promotionsCode);

        if ($promotionCode->isOutDated($this->dateTime)) {
            throw new PromotionCodeOutDatedException();
        }

        if ($promotionCode->isSoldOut()) {
            throw new PromotionCodeSoldOutException();
        }

        $cart = $this->getCart($sheet);
        $cart->setPromotionCode($promotionCode);

        // save data
        $this->save($cart);
        $this->promotionCodeRepository->set($promotionCode);
    }
}
