<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\User\Security;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Security\CanPasswordBeDefinedWithActivationEmail;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class CanPasswordBeDefinedWithActivationEmailTest extends TestCase
{
    /** @var ObjectProphecy|ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var ObjectProphecy|UserRepositoryInterface */
    private $userRepository;

    /** @var ObjectProphecy|CanPasswordBeDefinedWithActivationEmail */
    private $canPasswordBeDefinedWithActivationEmail;

    /** @var ObjectProphecy|Event */
    private $event;

    /** @var string */
    private $email;

    /** @var User */
    private $userWithPassword;

    /** @var User */
    private $userWithoutPassword;

    /** @var ObjectProphecy|Participant */
    private $participant;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->email = 'test@idontknow.why';
        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $this->userRepository = $this->prophesize(UserRepositoryInterface::class);
        $this->userWithPassword = UserFactory::create();
        $this->userWithoutPassword = UserFactory::createWithEmptyPassword($this->email);
        $this->participant = $this->prophesize(Participant::class);
        $this->canPasswordBeDefinedWithActivationEmail = new CanPasswordBeDefinedWithActivationEmail(
            $this->participantRepository->reveal(), $this->userRepository->reveal()
        );
    }

    public function testUserNotFound(): void
    {
        $this->userRepository
            ->findByEmail($this->email)
            ->shouldBeCalled()
            ->willReturn(null);

        $this->assertFalse(
            $this->canPasswordBeDefinedWithActivationEmail->isSatisfiedBy($this->event->reveal(), $this->email)
        );
    }

    public function testUserHasPassword(): void
    {
        $this->userRepository
            ->findByEmail($this->email)
            ->shouldBeCalled()
            ->willReturn($this->userWithPassword);

        $this->assertFalse(
            $this->canPasswordBeDefinedWithActivationEmail->isSatisfiedBy($this->event->reveal(), $this->email)
        );
    }

    public function testParticipantIsNotImported(): void
    {
        $this->userRepository
            ->findByEmail($this->email)
            ->shouldBeCalled()
            ->willReturn($this->userWithoutPassword);

        $this->participantRepository->getAllParticipantForUser($this->event->reveal(), $this->userWithoutPassword)
            ->shouldBeCalled()
            ->willReturn([$this->participant]);

        $this->participant->isImported()->shouldBeCalled()->willReturn(false);

        $this->assertFalse(
            $this->canPasswordBeDefinedWithActivationEmail->isSatisfiedBy($this->event->reveal(), $this->email)
        );
    }

    public function testParticipantIsImported(): void
    {
        $this->userRepository
            ->findByEmail($this->email)
            ->shouldBeCalled()
            ->willReturn($this->userWithoutPassword);

        $this->participantRepository->getAllParticipantForUser($this->event->reveal(), $this->userWithoutPassword)
            ->shouldBeCalled()
            ->willReturn([$this->participant]);

        $this->participant->isImported()->shouldBeCalled()->willReturn(true);

        $this->assertTrue(
            $this->canPasswordBeDefinedWithActivationEmail->isSatisfiedBy($this->event->reveal(), $this->email)
        );
    }
}
