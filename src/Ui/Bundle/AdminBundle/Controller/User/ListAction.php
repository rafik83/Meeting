<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\User;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\ConditionRules\Rules\GetRulesByTypeAndLocaleQuery;
use Proximum\Vimeet\Application\Query\User\UserEventListViews\GetUserEventListViewsQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class ListAction
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

    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ADMIN')
            || !$this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $page = $request->query->getInt('page', 1);
        $locale = $event->getAvailableLocale($request->getLocale());

        $userEventListViews = $this->queryBus->handle(
            new GetUserEventListViewsQuery($event, $page, $locale)
        );

        $rules = $this->queryBus->handle(new GetRulesByTypeAndLocaleQuery('user', $locale));

        return new Response(
            $this->engine->render(
                '@Admin/User/users-and-sheets-list.html.twig',
                [
                    'event' => $event,
                    'userEventListViews' => $userEventListViews,
                    'rules' => $rules,
                    'locale' => $locale,
                ]
            )
        );
    }
}
