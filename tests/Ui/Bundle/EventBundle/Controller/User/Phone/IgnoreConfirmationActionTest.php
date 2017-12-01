<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\User\Phone;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Command\User\Phone\IgnoreConfirmation;
use Proximum\Vimeet\Application\Command\User\Phone\IgnoreConfirmationHandler;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\User\Phone\IgnoreConfirmationAction;
use Symfony\Component\HttpFoundation\JsonResponse;

class IgnoreConfirmationActionTest extends TestCase
{
    public function testAction()
    {
        $event = EventFactory::createEvent();
        $participant = ParticipantFactory::create(SheetFactory::create($event));

        $ignoreConfirmation = new IgnoreConfirmation($event, $participant);
        $ignoreConfirmationHandler = $this->prophesize(IgnoreConfirmationHandler::class);
        $ignoreConfirmationHandler->handle($ignoreConfirmation)->shouldBeCalled();

        $authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')->shouldBeCalled();

        $action = new IgnoreConfirmationAction(
            $ignoreConfirmationHandler->reveal(),
            $authorizationChecker->reveal()
        );

        $response = $action(SheetFactory::create($event, $participant->getUser()), $participant);
        $expectedResponse = new JsonResponse([]);

        $this->assertEquals($expectedResponse, $response);

    }
}
