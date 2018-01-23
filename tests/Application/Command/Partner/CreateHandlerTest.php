<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Partner;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Command\Partner\Create;
use Proximum\Vimeet\Application\Command\Partner\CreateHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $event    = EventFactory::createEvent();
        $type     = new Type($event);

        $organizer = new Admin('test@test.com', '__salt__', null, 'fr', 'toto', 'tata', Admin::ROLE_ORGANIZER, $dateTime);

        $command            = new Create($organizer);
        $command->email     = 'partner@vimeet.com';
        $command->password  = 'password';
        $command->firstname = 'toto';
        $command->lastname  = 'tata';
        $command->types     = [$type];

        $partner         = new Admin('partner@vimeet.com', '__salt__', null, 'fr', 'toto', 'tata', Admin::ROLE_PARTNER, $dateTime);
        $expectedPartner = new Admin('partner@vimeet.com', '__salt__', 'encoded_password', 'fr', 'toto', 'tata', Admin::ROLE_PARTNER, $dateTime);

        $expectedPartner->addEvent($event);
        $expectedPartner->addType($type);

        // Mock
        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $passwordEncoder = $this->prophesize(PasswordEncoderInterface::class);
        $saltGenerator   = $this->prophesize(SaltGeneratorInterface::class);

        // Test
        $saltGenerator->generate()->shouldBeCalled()->willReturn('__salt__');

        $passwordEncoder->encode(Argument::that(function (Admin $user) use ($partner) {
            return $user->getEmail() === $partner->getEmail();
        }), $command->password)->shouldBeCalled()->willReturn('encoded_password');

        $adminRepository->emailExists($command->email)->shouldBeCalled();
        $adminRepository->add($expectedPartner)->shouldBeCalled();

        // Command
        $handler = new CreateHandler(
            $adminRepository->reveal(),
            $passwordEncoder->reveal(),
            $saltGenerator->reveal(),
            $dateTime
        );

        $handler->handle($command);
    }
}
