<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Type\PaymentConditions;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Type\PaymentConditions\Update;
use Proximum\Vimeet\Application\Command\Type\PaymentConditions\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Domain\Repository\Type\PaymentConditionsRepositoryInterface;

class UpdateHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $paymentConditionsRepository;

    /** @var ObjectProphecy */
    private $type;

    public function setUp()
    {
        $this->type = $this->prophesize(Type::class);
        $this->paymentConditionsRepository = $this->prophesize(PaymentConditionsRepositoryInterface::class);
    }

    public function testHandleNoSpecificConditions()
    {
        $this->type->getPaymentConditions()->willReturn(null);

        $this->paymentConditionsRepository->remove(Argument::any())->shouldNotBeCalled();
        $this->paymentConditionsRepository->add(Argument::any())->shouldNotBeCalled();
        $this->paymentConditionsRepository->set(Argument::any())->shouldNotBeCalled();

        $command = new Update($this->type->reveal());
        $command->specificPaymentConditions = false;
        $handler = new UpdateHandler($this->paymentConditionsRepository->reveal());
        $handler->handle($command);
    }

    public function testHandleRemovePaymentConditions()
    {
        $paymentConditions = $this->prophesize(Type\PaymentConditions::class);
        $paymentConditions->isAllowDeposit()->willReturn(false);
        $paymentConditions->getDepositUntil()->willReturn(null);
        $paymentConditions->getMinimumForDeposit()->willReturn(null);
        $paymentConditions->getDeposit()->willReturn(null);
        $paymentConditions->getPaymentModes()->willReturn([]);
        $this->type->getPaymentConditions()->willReturn($paymentConditions->reveal());

        $this->paymentConditionsRepository->remove($paymentConditions->reveal())->shouldBeCalled();
        $this->paymentConditionsRepository->set(Argument::any())->shouldNotBeCalled();
        $this->paymentConditionsRepository->add(Argument::any())->shouldNotBeCalled();

        $command = new Update($this->type->reveal());
        $command->specificPaymentConditions = false;
        $handler = new UpdateHandler($this->paymentConditionsRepository->reveal());
        $handler->handle($command);
    }

    public function testHandleUpdatePaymentConditions()
    {
        $dateTime = new \DateTime();
        $paymentConditions = $this->prophesize(Type\PaymentConditions::class);
        $paymentConditions->isAllowDeposit()->willReturn(false);
        $paymentConditions->getDepositUntil()->willReturn(null);
        $paymentConditions->getMinimumForDeposit()->willReturn(null);
        $paymentConditions->getDeposit()->willReturn(null);
        $paymentConditions->getPaymentModes()->willReturn([]);
        $this->type->getPaymentConditions()->willReturn($paymentConditions->reveal());

        $paymentConditions->update(
            [Mode::PAYMENT_PAYPAL],
            true,
            $dateTime,
            600,
            50
        )->shouldBeCalled();
        $this->paymentConditionsRepository->set($paymentConditions->reveal())->shouldBeCalled();
        $this->paymentConditionsRepository->add(Argument::any())->shouldNotBeCalled();
        $this->paymentConditionsRepository->remove(Argument::any())->shouldNotBeCalled();

        $command = new Update($this->type->reveal());
        $command->specificPaymentConditions = true;
        $command->allowDeposit = true;
        $command->depositUntil = $dateTime;
        $command->deposit = 50;
        $command->minimumForDeposit = 600;
        $command->paymentModes = [Mode::PAYMENT_PAYPAL];
        $handler = new UpdateHandler($this->paymentConditionsRepository->reveal());
        $handler->handle($command);
    }

    public function testHandleCreatePaymentConditions()
    {
        $dateTime = new \DateTime();
        $this->type->getPaymentConditions()->willReturn(null);

        $expected = new Type\PaymentConditions(
            $this->type->reveal(),
            [Mode::PAYMENT_PAYPAL],
            true,
            $dateTime,
            600,
            50
        );
        $this->paymentConditionsRepository->add($expected)->shouldBeCalled();
        $this->paymentConditionsRepository->set(Argument::any())->shouldNotBeCalled();
        $this->paymentConditionsRepository->remove(Argument::any())->shouldNotBeCalled();

        $command = new Update($this->type->reveal());
        $command->specificPaymentConditions = true;
        $command->allowDeposit = true;
        $command->depositUntil = $dateTime;
        $command->deposit = 50;
        $command->minimumForDeposit = 600;
        $command->paymentModes = [Mode::PAYMENT_PAYPAL];
        $handler = new UpdateHandler($this->paymentConditionsRepository->reveal());
        $handler->handle($command);
    }
}
