<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\Session;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Session\UpdateParticipantSessionManager;

class UpdateParticipantSessionManagerTest extends TestCase
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var UpdateParticipantSessionManager
     */
    private $sessionManager;

    public function setUp()
    {
        $this->session        = $this->prophesize(SessionInterface::class);
        $this->sessionManager = new UpdateParticipantSessionManager($this->session->reveal());
    }

    public function testGetMobile()
    {
        $this->session->get(UpdateParticipantSessionManager::UPDATE_PARTICIPANT_SESSION_DATA)
            ->shouldBeCalled()
            ->willReturn([
                UpdateParticipantSessionManager::UPDATE_PARTICIPANT__MOBILE => '010203040506'
            ]);

        $this->assertEquals('010203040506', $this->sessionManager->getMobile());
    }

    public function testGetSheet()
    {
        $this->session->get(UpdateParticipantSessionManager::UPDATE_PARTICIPANT_SESSION_DATA)
            ->shouldBeCalled()
            ->willReturn([
                UpdateParticipantSessionManager::UPDATE_PARTICIPANT__SHEET => 1
            ]);

        $this->assertEquals('1', $this->sessionManager->getSheet());
    }

    public function testGetParticipant()
    {
        $this->session->get(UpdateParticipantSessionManager::UPDATE_PARTICIPANT_SESSION_DATA)
            ->shouldBeCalled()
            ->willReturn([
                UpdateParticipantSessionManager::UPDATE_PARTICIPANT__PARTICIPANT => 21
            ]);

        $this->assertEquals('21', $this->sessionManager->getParticipant());
    }
}
