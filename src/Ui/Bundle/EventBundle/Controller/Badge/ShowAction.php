<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Badge;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Badge\GetUserBadgeByEventQuery;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class ShowAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        EngineInterface $engine,
        QueryBusInterface $queryBus
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->engine = $engine;
        $this->queryBus = $queryBus;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        User $user,
        Sheet $sheet
    ): Response {
        if (!$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)) {
            throw new AccessDeniedException();
        }

        $event = $eventDomain->getEvent();

        return new Response(
            $this->engine->render(
                'EventBundle:Badge:show.html.twig',
                [
                    'event' => $event,
                    'sheet' => $sheet,
                    'userBadgeByEventView' => $this->queryBus->handle(
                        new GetUserBadgeByEventQuery($event, $user)
                    ),
                ]
            )
        );
    }
}
