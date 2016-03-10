<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Operator;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Command\Operator\Create;
use Proximum\Vimeet\Application\Command\Operator\CreateHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleWithEvents()
    {
        $event     = new Event();
        $event2    = new Event();
        $organizer = new Admin('test@test.com', '__salt__', null, 'fr', 'toto', 'tata', Admin::ROLE_ORGANIZER);
        $organizer->addEvent($event);
        $organizer->addEvent($event2);

        $command            = new Create($organizer);
        $command->email     = 'test2@test.com';
        $command->password  = 'password';
        $command->firstname = 'toto';
        $command->lastname  = 'tata';

        $operator = new Admin('test2@test.com', '__salt__', null, 'fr', 'toto', 'tata', Admin::ROLE_OPERATOR);
        $expectedOperator = new Admin('test2@test.com', '__salt__', 'encoded_password', 'fr', 'toto', 'tata', Admin::ROLE_OPERATOR);
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

        $handler = new CreateHandler($adminRepository->reveal(), $passwordEncoder->reveal(), $saltGenerator->reveal());
        $handler->handle($command);
    }
}
