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
use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Application\Query\ConditionRules\Filters\GetFiltersByTypeAndLocaleQuery;
use Proximum\Vimeet\Application\Query\User\UserEventListViews\GetUserEventListViewsQuery;
use Proximum\Vimeet\Domain\ConditionRules\Storage\RuleStorageInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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

    /** @var SessionInterface */
    private $session;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /** @var RuleStorageInterface */
    private $ruleStorageInterface;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        EngineInterface $engine,
        QueryBusInterface $queryBus,
        SessionInterface $session,
        UrlGeneratorInterface $urlGenerator,
        RuleStorageInterface $ruleStorageInterface
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->engine = $engine;
        $this->queryBus = $queryBus;
        $this->session = $session;
        $this->urlGenerator = $urlGenerator;
        $this->ruleStorageInterface = $ruleStorageInterface;
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

        if (1 === $request->query->getInt('reset')) {
            $this->ruleStorageInterface->removeRules($event, 'user');

            return new RedirectResponse(
                $this->urlGenerator->generate('admin_users_list', ['event' => $event->getId()])
            );
        }

        if ($request->query->get('rules')) {
            $this->ruleStorageInterface->saveRules($event, 'user', $request->query->get('rules'));
        }

        $userEventListViews = $this->queryBus->handle(
            new GetUserEventListViewsQuery(
                $event,
                $page,
                $locale,
                $this->ruleStorageInterface->getRules($event, 'user')
            )
        );

        $filters = $this->queryBus->handle(new GetFiltersByTypeAndLocaleQuery($event, 'user', $request->getLocale()));

        return new Response(
            $this->engine->render(
                '@Admin/User/users-and-sheets-list.html.twig',
                [
                    'event' => $event,
                    'userEventListViews' => $userEventListViews,
                    'filters' => $filters,
                    'rules' => $this->ruleStorageInterface->getRulesQuery($event, 'user'),
                    'locale' => $request->getLocale(),
                ]
            )
        );
    }
}
