<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Meeting;

use DateTime;
use Proximum\Vimeet\Application\Command\Meeting\RefuseRequest;
use Proximum\Vimeet\Application\Command\Meeting\RefuseRequestHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class RefuseRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event     = new Event();
        $type      = new Type($event);
        $sheetTo   = new Sheet($event, $type, [], []);
        $sheetFrom = new Sheet($event, $type, [], []);
        $dateTime  = new DateTime;

        $request         = new Request($sheetFrom, [], $sheetTo, 'test', $dateTime);
        $expectedRequest = new Request($sheetFrom, [], $sheetTo, 'test', $dateTime);
        $expectedRequest->setState(Request::STATE_REFUSED);
        $expectedRequest->setRefuseMessage('this is a test');

        $refusedRequest = new RefuseRequest($request);
        $refusedRequest->refuseMessage = 'this is a test';

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();

        $handler = new RefuseRequestHandler($requestRepository->reveal());
        $handler->handle($refusedRequest);
    }
}
