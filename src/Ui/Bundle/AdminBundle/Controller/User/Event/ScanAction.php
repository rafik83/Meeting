<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\User\Event;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\User\Event\ScanCommand;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierToUserQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ScanAction
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus
    ) {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }

    public function __invoke(Request $request, Event $event): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['identifier'], $data['scannedAt'])) {
            return new JsonResponse('Bad parameters', 400);
        }

        $user = $this->queryBus->handle(new QRCodeIdentifierToUserQuery($data['identifier']));

        if (!$user instanceof User) {
            return new JsonResponse('User not found', 400);
        }

        $this->commandBus->handle(
            new ScanCommand(
                $event,
                $user,
                new \DateTime($data['scannedAt'])
            )
        );

        return new JsonResponse('ok');
    }
}
