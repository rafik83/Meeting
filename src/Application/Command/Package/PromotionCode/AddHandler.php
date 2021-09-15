<?php

namespace Proximum\Vimeet\Application\Command\Package\PromotionCode;

use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeAlreadyExistException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeConflictException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeNotFoundException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeOutDatedException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeSoldOutException;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class AddHandler
{
    /** @var CartManager */
    private $cartManager;

    /** @var PromotionCodeRepositoryInterface */
    private $promotionCodeRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var Merger */
    private $orderMerger;

    /**
     * @param CartManager                      $cartManager
     * @param PromotionCodeRepositoryInterface $promotionCodeRepository
     * @param \DateTimeInterface               $dateTime
     * @param Merger                           $orderMerger
     */
    public function __construct(
        CartManager $cartManager,
        PromotionCodeRepositoryInterface $promotionCodeRepository,
        \DateTimeInterface $dateTime,
        Merger $orderMerger
    ) {
        $this->cartManager             = $cartManager;
        $this->promotionCodeRepository = $promotionCodeRepository;
        $this->dateTime                = $dateTime;
        $this->orderMerger             = $orderMerger;
    }

    /**
     * @param Add $add
     *
     * @throws PromotionCodeNotFoundException
     * @throws PromotionCodeOutDatedException
     * @throws PromotionCodeSoldOutException
     * @throws PromotionCodeAlreadyExistException
     * @throws PromotionCodeConflictException
     */
    public function handle(Add $add)
    {
        $cart = $this->cartManager->getCart($add->sheet);

        $promotionCode = $this->promotionCodeRepository->findByEventAndCode(
            $add->sheet->getEvent(),
            $add->promotionCode
        );

        if (null === $promotionCode) {
            throw new PromotionCodeNotFoundException('The promotion code is not found');
        }

        if ($add->sheet->hasNotCancelledOrders()) {
            $order = $this->orderMerger->merge($add->sheet->getNotCancelledOrders());

            if ($order->hasPromotionCode($promotionCode)) {
                throw new PromotionCodeAlreadyExistException('This promotion code is already used');
            }
        }

        $cart->apply($promotionCode, $this->dateTime);

        $this->cartManager->save($cart);
    }
}
