<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Messaging\Campaign;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\SelectMessage;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\SelectMessageHandler;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SelectMessageHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Input context
        
        $event     = EventFactory::createEvent();
        $createdAt = new \DateTime();
        $message   = new Message($event, $createdAt, 'test', 'test subject', 'test content');

        $campaign  = new Campaign($event, 'amazing campaign', [], $createdAt);

        $expectedCampaign = new Campaign($event, '', [], $createdAt);

        // Mock

        $campaignRepository = $this->prophesize(CampaignRepositoryInterface::class);

        // Scenario

        $campaignRepository->set($message)->shouldBeCalled();

        // Command

        $command = new SelectMessage($campaign);

        // Handler

        $handler = new SelectMessageHandler($campaignRepository);

        $handler->handle($command);




        //set -> should be called or should not be called pour prophesize
    }

}
