<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Agenda\AvailableSheets;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\RequestApprovedAndRefusedQuery;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\RequestApprovedAndRefusedQueryHandler;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class RequestApprovedAndRefusedQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $createdAt = new \DateTime();
        $user = UserFactory::create();
        $event = EventFactory::createEvent();
        $fromSheet = SheetFactory::create();
        $toSheet = SheetFactory::create();
        $fromParticipants = [
            ParticipantFactory::create($fromSheet),
            ParticipantFactory::create($fromSheet),
        ];
        $toPartcipants = [
            ParticipantFactory::create($toSheet),
            ParticipantFactory::create($toSheet),
        ];
        $approvedRequest = [
            new Request(
                $fromSheet,
                $fromParticipants,
                $toSheet,
                $toPartcipants,
                $createdAt,
                $user,
                $event
            )
        ];
        $refusedRequest = [
            new Request(
                $toSheet,
                $toPartcipants,
                $fromSheet,
                $fromParticipants,
                $createdAt,
                $user,
                $event
            )
        ];

        $requestRepository
            ->getAllRequestBySheet($fromSheet, [
                'state' => Request::STATE_APPROVED,
            ])
            ->shouldBeCalled()
            ->willReturn($approvedRequest);

        $requestRepository
            ->getAllRequestBySheet($fromSheet, [
                'state' => Request::STATE_REFUSED,
            ])
            ->shouldBeCalled()
            ->willReturn($refusedRequest);

        $query = new RequestApprovedAndRefusedQuery($fromSheet);
        $handler = new RequestApprovedAndRefusedQueryHandler($requestRepository->reveal());

        $result = $handler->handle($query);

        $this->assertEquals(
            array_merge($approvedRequest, $refusedRequest),
            $result
        );
    }
}
