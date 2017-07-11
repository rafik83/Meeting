<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Service\HomeDispatch;

use Proximum\Vimeet\Application\Components\Home\HomeDispatch;
use Proximum\Vimeet\Application\Components\Home\HomeDispatchAnonymousUser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Adapter\AuthorizationCheckerAdapter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\User\UserInterface;

class HomeUserDispatcher
{
    /** @var Router */
    private $router;

    /** @var HomeDispatch */
    private $homeDispatch;

    /** @var HomeDispatchAnonymousUser */
    private $homeDispatchAnonymousUser;

    /** @var AuthorizationCheckerAdapter */
    private $authorizationChecker;

    /**
     * HomeUserDispatcher constructor.
     *
     * @param Router $router
     * @param HomeDispatch $homeDispatch
     * @param HomeDispatchAnonymousUser $homeDispatchAnonynmousUser
     * @param AuthorizationCheckerAdapter $authorizationChecker
     */
    public function __construct(
        Router $router,
        HomeDispatch $homeDispatch,
        HomeDispatchAnonymousUser $homeDispatchAnonynmousUser,
        AuthorizationCheckerAdapter $authorizationChecker
    ) {
        $this->router = $router;
        $this->homeDispatch = $homeDispatch;
        $this->homeDispatchAnonymousUser = $homeDispatchAnonynmousUser;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param Event              $event
     * @param null|UserInterface $user
     *
     * @return Response|null
     */
    public function attemptDispatchUser(Event $event, UserInterface $user = null): ? Response
    {
        if (null !== $response = $this->attemptDispatchAnonymousUser($event)) {
            return $response;
        }

        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') && $user instanceof User) {
            if (null !== $response = $this->attemptDispatchLoggedUser($event, $user)) {
                return $response;
            }
        }

        return null;
    }

    /**
     * @param Event              $event
     * @param null|UserInterface $user
     *
     * @return null|Response
     */
    private function attemptDispatchLoggedUser(Event $event, UserInterface $user = null)
    {
        $homeDispatchView = $this->homeDispatch->handle($event, $user);

        if ($homeDispatchView->isGroup()) {
            return new RedirectResponse($this->router->generate(
                'event_sheet_group_index',
                ['sheetGroup' => $homeDispatchView->getGroup()->getId()]
            ));
        }

        if ($homeDispatchView->isOneSheet()) {
            return new RedirectResponse($this->router->generate(
                'event_sheet_default',
                ['sheet' => $homeDispatchView->getSheet()->getId()]
            ));
        }

        if ($homeDispatchView->isMultipleSheet()) {
            return new RedirectResponse($this->router->generate('event_select_sheet'));
        }

        return null;
    }

    /**
     * @param Event $event
     *
     * @return null|Response
     */
    private function attemptDispatchAnonymousUser(Event $event): ? Response
    {
        $homeDispatchView = $this->homeDispatchAnonymousUser->handle($event);

        if ($homeDispatchView->isRegistrationNotOpen() || $homeDispatchView->isRegistrationClosed()) {
            return new RedirectResponse($this->router->generate('event_waiting_page'));
        }

        return null;
    }
}
