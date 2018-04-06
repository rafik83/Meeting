<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Cart;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\Converter;
use Proximum\Vimeet\Domain\Model\Address;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\PromotionCodeRow;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\ParticipantProductSetter;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\CartStepRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRowRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ConverterTest extends TestCase
{
    public function testToOrder()
    {
        $datetime = new \DateTime();
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $owner    = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $package  = new Package($event, 'title', $datetime);
        $sheet    = new Sheet($event, $type, [], $owner, $datetime);
        $type->setPackage($package);
        $billingInfo = new BillingInfo($sheet);
        $billingInfo->update(
            'gender',
            'lastname',
            'firstname',
            'function',
            'phone',
            'mobile',
            'company',
            'email@email.com',
            new Address('street', 'zipcode', 'city', 'FR'),
            'vatNumber',
            'Patrick sebastien'
        );

        $plan = Product::createPlan($event, 'plan', '', 200, 20, 20, 100);
        $plan->translate('fr', 'plan', '', '', '', '');
        $plan->translate('en', 'plan', '', '', '', '');
        $chair = Product::createOption($event, 'chair', '', 100, 20, 2, 20, 100, true);
        $chair->translate('fr', 'chair', '', '', '', '');
        $chair->translate('en', 'chair', '', '', '', '');

        $promotionCode    = new PromotionCode($event, 'title', 'code', 20, null);
        $promotionCode->setPromotion($chair, Promotion::TYPE_PERCENT_OFF, 50);
        $promotionCodeRow = new PromotionCodeRow($sheet, $promotionCode);

        $planRow     = new CartRow($sheet, $plan, 1);
        $chairRow    = new CartRow($sheet, $chair, 2);
        $currentStep = 4;
        $cart  = new Cart($sheet, [$planRow, $chairRow], [$promotionCodeRow], $currentStep);

        $expectedPromotionCode = new PromotionCode($event, 'title', 'code', 19, null);
        $expectedPromotionCode->setPromotion($chair, Promotion::TYPE_PERCENT_OFF, 50);

        $groupsData    = '';
        $order         = new Order($sheet, $groupsData, $datetime);
        $planOrderRow  = new Order\Row($order, 1, 20, $plan);
        $chairOrderRow = new Order\Row($order, 2, 20, $chair);
        $promotionCodeOrderRow = new Order\PromotionCode($order, $promotionCode, -100, 20);
        $order->addRow($planOrderRow);
        $order->addRow($chairOrderRow);
        $order->addPromotionCode($promotionCodeOrderRow);

        // Mock
        $orderRepository            = $this->prophesize(OrderRepositoryInterface::class);
        $cartRowRepository          = $this->prophesize(CartRowRepositoryInterface::class);
        $cartStepRepository         = $this->prophesize(CartStepRepositoryInterface::class);
        $promotionCodeRowRepository = $this->prophesize(PromotionCodeRowRepositoryInterface::class);
        $promotionCodeRepository    = $this->prophesize(PromotionCodeRepositoryInterface::class);
        $participantProductSetter   = $this->prophesize(ParticipantProductSetter::class);

        $orderRepository->add(Argument::that(function (Order $givenOrder) use ($order) {
            return \count($givenOrder->getRows()) === \count($order->getRows())
                && (float) $givenOrder->getTotalWithoutVat() === (float) $order->getTotalWithoutVat()
                && \count($givenOrder->getPromotionCodes()) === \count($order->getPromotionCodes());
        }))->shouldBeCalled();

        $cartRowRepository->deleteForSheet($sheet)->shouldBeCalled();
        $cartStepRepository->deleteForSheet($sheet)->shouldBeCalled();
        $promotionCodeRowRepository->deleteForSheet($sheet)->shouldBeCalled();
        $promotionCodeRepository->set($expectedPromotionCode)->shouldBeCalled();

        $converter = new Converter(
            $orderRepository->reveal(),
            $cartRowRepository->reveal(),
            $cartStepRepository->reveal(),
            $promotionCodeRowRepository->reveal(),
            $promotionCodeRepository->reveal(),
            $participantProductSetter->reveal(),
            $datetime
        );

        $converter->toOrder($cart);
    }
}
