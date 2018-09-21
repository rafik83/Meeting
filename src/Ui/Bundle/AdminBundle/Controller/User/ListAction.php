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
use Proximum\Vimeet\Application\Query\ConditionRules\Rules\GetConditionRulesQuery;
use Proximum\Vimeet\Application\Query\User\UserEventListViews\GetUserEventListViewsQuery;
use Proximum\Vimeet\Domain\ConditionRules\View\RuleInterface;
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

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        EngineInterface $engine,
        QueryBusInterface $queryBus,
        SessionInterface $session,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->engine = $engine;
        $this->queryBus = $queryBus;
        $this->session = $session;
        $this->urlGenerator = $urlGenerator;
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
            $this->session->remove($this->getRulesKey($event));

            return new RedirectResponse(
                $this->urlGenerator->generate('admin_users_list', ['event' => $event->getId()])
            );
        }

        if ('POST' === $request->getMethod() && $request->request->get('rules')) {
            $this->session->set($this->getRulesKey($event), $request->request->get('rules'));
        }

        $userEventListViews = $this->queryBus->handle(
            new GetUserEventListViewsQuery($event, $page, $locale, $this->getRules($event))
        );

        $filters = $this->queryBus->handle(new GetFiltersByTypeAndLocaleQuery('user', $request->getLocale()));

        return new Response(
            $this->engine->render(
                '@Admin/User/users-and-sheets-list.html.twig',
                [
                    'event' => $event,
                    'userEventListViews' => $userEventListViews,
                    'filters' => $filters,
                    'rules' => $this->session->get($this->getRulesKey($event)),
                    'locale' => $request->getLocale(),
                ]
            )
        );
    }

    private function getRules(Event $event): ?RuleInterface
    {
        $rules = json_decode($this->session->get($this->getRulesKey($event)), true);

        if ($rules) {
            return $this->queryBus->handle(new GetConditionRulesQuery($rules));
        }

        return null;
    }

    private function getRulesKey(Event $event): string
    {
        return sprintf('rules_%s', $event->getId());
    }
}
