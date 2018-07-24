<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Event\GetQRCodePayloadByEventQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class QRCodeReaderAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(EngineInterface $engine, QueryBusInterface $queryBus)
    {
        $this->engine = $engine;
        $this->queryBus = $queryBus;
    }

    public function __invoke(Request $request, Event $event): Response
    {
        $qrCodePayloads = $this->queryBus->handle(
            new GetQRCodePayloadByEventQuery(
                $event,
                $event->getAvailableLocale($request->getLocale())
            )
        );

        return new Response(
            $this->engine->render('@Admin/Event/qrCodeReader.html.twig', [
                'event' => $event,
                'qrCodePayloads' => json_encode($qrCodePayloads),
            ])
        );
    }
}
