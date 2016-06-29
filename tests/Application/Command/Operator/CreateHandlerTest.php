<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Operator;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Command\Operator\Create;
use Proximum\Vimeet\Application\Command\Operator\CreateHandler;
use Proximum\Vimeet\Application\Components\Token\Admin\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Event\Admin\ActivateAccountEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Admin\ActivateAccountToken;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleWithEvents()
    {
        $dateTime  = new \DateTime();
        $event     = EventFactory::createEvent();
        $event2    = EventFactory::createEvent();
        $organizer = new Admin('test@test.com', '__salt__', null, 'fr', 'toto', 'tata', Admin::ROLE_ORGANIZER, $dateTime);
        $organizer->addEvent($event);
        $organizer->addEvent($event2);

        $command            = new Create($organizer, $dateTime);
        $command->email     = 'test2@test.com';
        $command->password  = 'password';
        $command->firstname = 'toto';
        $command->lastname  = 'tata';

        $operator = new Admin('test2@test.com', '__salt__', null, 'fr', 'toto', 'tata', Admin::ROLE_OPERATOR, $dateTime);
        $expectedOperator = new Admin('test2@test.com', '__salt__', 'encoded_password', 'fr', 'toto', 'tata', Admin::ROLE_OPERATOR, $dateTime);
        $expectedOperator->addEvent($event);
        $expectedOperator->addEvent($event2);

        $saltGenerator = $this->prophesize(SaltGeneratorInterface::class);
        $saltGenerator->generate()->shouldBeCalled()->willReturn('__salt__');

        $passwordEncoder = $this->prophesize(PasswordEncoderInterface::class);
        $passwordEncoder->encode(Argument::that(function (Admin $operator) use ($operator) {
            return $operator->getEmail() === $operator->getEmail();
        }), $command->password)->shouldBeCalled()->willReturn('encoded_password');

        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->emailExists($command->email)->shouldBeCalled()->willReturn(false);
        $adminRepository->add($expectedOperator)->shouldBeCalled();

        $activateAccountTokenGenerator  = $this->prophesize(ActivateAccountTokenGenerator::class);
        $eventDispatcher                = $this->prophesize(EventDispatcherInterface::class);

        $expectedActivateAccountToken = new ActivateAccountToken(
            $expectedOperator,
            'STRING',
            new \DateTime()
        );

        $activateAccountEvent = new ActivateAccountEvent(
            $expectedOperator,
            $expectedActivateAccountToken,
            'fr'
        );

        $activateAccountTokenGenerator->generate($expectedOperator)->shouldBeCalled()->willReturn($expectedActivateAccountToken);
        $eventDispatcher->dispatch('admin_activate_account', $activateAccountEvent)->shouldBeCalled();

        $handler = new CreateHandler(
            $adminRepository->reveal(),
            $passwordEncoder->reveal(),
            $saltGenerator->reveal(),
            $activateAccountTokenGenerator->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($command);
    }
}
