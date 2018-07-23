<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Visio\Test;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\VideoConference\RequestTestAccess;
use Proximum\Vimeet\Application\Exception\VideoConference\InvalidTokenGeneratorArgumentsException;
use Proximum\Vimeet\Application\View\Meeting\VideoConferenceView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TestNetworkSessionAction
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var EngineInterface */
    private $engine;

    public function __construct(CommandBusInterface $commandBus, EngineInterface $engine)
    {
        $this->commandBus = $commandBus;
        $this->engine = $engine;
    }

    public function __invoke(EventDomain $eventDomain, string $sessionId): Response
    {
        try {
            /** @var VideoConferenceView $videoConferenceView */
            $videoConferenceView = $this->commandBus->handle(
                new RequestTestAccess($sessionId)
            );
        } catch (InvalidTokenGeneratorArgumentsException $exception) {
            throw new NotFoundHttpException('The sessionId is not valid');
        }

        return $this->engine->renderResponse(
            'EventBundle:Visio/Test:testNetworkAudioVideo.html.twig', [
                'event' => $eventDomain->getEvent(),
                'videoConferenceView' => $videoConferenceView,
            ]
        );
    }
}
