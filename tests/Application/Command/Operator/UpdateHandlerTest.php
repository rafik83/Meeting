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
use Proximum\Vimeet\Application\Command\Operator\Update;
use Proximum\Vimeet\Application\Command\Operator\UpdateHandler;
use Proximum\Vimeet\Application\Components\Token\Admin\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Event\Admin\ActivateAccountEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Admin\ActivateAccountToken;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $dateTime  = new \DateTime();
        $event     = new Event();
        $event2    = new Event();
        $event3    = new Event();
        $operator  = new Admin('test2@test.com', '__salt__', 'encoded_password', 'fr', 'toto', 'tata', Admin::ROLE_OPERATOR, $dateTime);
        $operator->addEvent($event);
        $operator->addEvent($event2);

        $command            = new Update($operator);
        $command->email     = 'test2@test.com';
        $command->firstname = 'truc';
        $command->lastname  = 'muche';
        $command->events[]  = $event;
        $command->events[]  = $event3;

        $expectedOperator = new Admin('test2@test.com', '__salt__', 'encoded_password', 'fr', 'truc', 'muche', Admin::ROLE_OPERATOR, $dateTime);
        $expectedOperator->addEvent($event);
        $expectedOperator->addEvent($event3);

        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->emailExists($command->email)->shouldNotBeCalled();
        $adminRepository->set($expectedOperator)->shouldBeCalled();

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

        $activateAccountTokenGenerator->generate($expectedOperator)->shouldNotBeCalled();
        $eventDispatcher->dispatch('admin_activate_account', $activateAccountEvent)->shouldNotBeCalled();

        $handler = new UpdateHandler(
            $adminRepository->reveal(),
            $activateAccountTokenGenerator->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($command);
    }

    public function testHandleWithNewMail()
    {
        $dateTime  = new \DateTime();
        $event     = new Event();
        $event2    = new Event();
        $event3    = new Event();
        $operator  = new Admin('test@test.com', '__salt__', 'encoded_password', 'fr', 'toto', 'tata', Admin::ROLE_OPERATOR, $dateTime);
        $operator->addEvent($event);
        $operator->addEvent($event2);

        $command            = new Update($operator);
        $command->email     = 'test2@test.com';
        $command->firstname = 'truc';
        $command->lastname  = 'muche';
        $command->events[]  = $event;
        $command->events[]  = $event3;

        $expectedOperator = new Admin('test2@test.com', '__salt__', 'encoded_password', 'fr', 'truc', 'muche', Admin::ROLE_OPERATOR, $dateTime);
        $expectedOperator->addEvent($event);
        $expectedOperator->addEvent($event3);

        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->emailExists($command->email)->shouldBeCalled()->willReturn(false);
        $adminRepository->set($expectedOperator)->shouldBeCalled();

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

        $handler = new UpdateHandler(
            $adminRepository->reveal(),
            $activateAccountTokenGenerator->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($command);
    }
}
